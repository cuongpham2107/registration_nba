<?php

namespace App\Support;

use App\Models\GatheringPointFee;
use App\Models\LiftingServiceFee;
use App\Models\RegistrationEntry;
use Carbon\Carbon;

class FeeCalculator
{
    /**
     * Contract:
     * - Input: RegistrationEntry (expects actual_date_in, actual_date_out? and vehicleRegistration relations)
     * - Output: array{gathering:int,lifting:int,total:int,meta:array}
     */
    public static function forRegistrationEntry(RegistrationEntry $entry): array
    {
        $entryTime = $entry->actual_date_in;
        $exitTime = $entry->actual_date_out ?? now();

        $vehicleRegistration = $entry->vehicleRegistration;
        if (! $vehicleRegistration) {
            return self::empty();
        }

        $gatheringFee = $vehicleRegistration->gatheringPointFee;
        $liftingFee = null;

        if (filled($vehicleRegistration->lifting_service_fee_id)) {
            $liftingFee = LiftingServiceFee::query()
                ->whereKey($vehicleRegistration->lifting_service_fee_id)
                ->where('is_active', true)
                ->first();
        }

        $gatheringAmount = self::calculateGathering(
            fee: $gatheringFee,
            entryTime: $entryTime,
            exitTime: $exitTime,
        );

        // $liftingAmount = self::calculateLifting(
        //     fee: $liftingFee,
        //     countPackage: (int) ($vehicleRegistration->count_package ?? 0),
        //     entryTime: $entryTime,
        //     exitTime: $exitTime,
        // );

        return [
            'gathering' => $gatheringAmount,
            // 'lifting' => $liftingAmount,
            // 'total' => $gatheringAmount + $liftingAmount,
            'total' => $gatheringAmount,
            'meta' => [
                'entry_time' => $entryTime,
                'exit_time' => $exitTime,
            ],
        ];
    }

    public static function calculateGathering(?GatheringPointFee $fee, $entryTime, $exitTime): int
    {
        if (! $fee || blank($entryTime) || blank($exitTime)) {
            return 0;
        }

        $entry = Carbon::parse($entryTime);
        $exit = Carbon::parse($exitTime);
        if ($exit->lessThan($entry)) {
            [$entry, $exit] = [$exit, $entry];
        }

        // Rules:
        // - 07:00 -> 12:00 : morning_fee
        // - 12:00 -> 17:00 : afternoon_fee
        // - 07:00 -> 17:00 (covers both peaks) : full_day_fee
        // - 17:00 -> 07:00 next day (any overlap) : night_fee

        $entryDate = $entry->copy()->startOfDay();

        $morningStart = $entryDate->copy()->setTime(7, 0);
        $noon = $entryDate->copy()->setTime(12, 0);
        $afternoonEnd = $entryDate->copy()->setTime(17, 0);
        $nextMorningStart = $entryDate->copy()->addDay()->setTime(7, 0);

        $usesMorning = self::overlaps($entry, $exit, $morningStart, $noon);
        $usesAfternoon = self::overlaps($entry, $exit, $noon, $afternoonEnd);
        $usesNight = self::overlaps($entry, $exit, $afternoonEnd, $nextMorningStart);

        if ($usesNight) {
            return (int) ($fee->night_fee ?? 0);
        }

        if ($usesMorning && $usesAfternoon) {
            return (int) ($fee->full_day_fee ?? 0);
        }

        if ($usesMorning) {
            return (int) ($fee->morning_fee ?? 0);
        }

        if ($usesAfternoon) {
            return (int) ($fee->afternoon_fee ?? 0);
        }

        // If it doesn't fall into any window (e.g. before 07:00 but exits before 07:00), treat as night.
        return (int) ($fee->night_fee ?? 0);
    }

    // public static function calculateLifting(?LiftingServiceFee $fee, int $countPackage, $entryTime, $exitTime): int
    // {
    //     if (! $fee || blank($entryTime) || blank($exitTime)) {
    //         return 0;
    //     }

    //     $entry = Carbon::parse($entryTime);
    //     $exit = Carbon::parse($exitTime);
    //     if ($exit->lessThan($entry)) {
    //         [$entry, $exit] = [$exit, $entry];
    //     }

    //     // Working window for lifting:
    //     // - Regular: 07:30 -> 16:30
    //     // - After-hours: 16:30 -> 07:30 next day => base + 150% (i.e. *2.5) according to requirement.

    //     $baseDate = $entry->copy()->startOfDay();
    //     $regularStart = $baseDate->copy()->setTime(7, 30);
    //     $regularEnd = $baseDate->copy()->setTime(16, 30);
    //     $nextRegularStart = $baseDate->copy()->addDay()->setTime(7, 30);

    //     $isAfterHours = self::overlaps($entry, $exit, $regularEnd, $nextRegularStart);

    //     $afterHoursPercent = (int) ($fee->after_hours_fee ?? 150); // seed: 150
    //     $multiplier = $isAfterHours ? (1 + ($afterHoursPercent / 100)) : 1; // base + 150% => 2.5

    //     if ($fee->weight_category === 'under_2_tons') {
    //         $perPackage = (int) ($fee->regular_hours_fee ?? 0);
    //         $packages = max(0, $countPackage);
    //         $base = $perPackage * $packages;

    //         return (int) round($base * $multiplier);
    //     }

    //     // over_2_tons
    //     $minutes = $entry->diffInMinutes($exit);

    //     // Requirement: 1 ca 4h => 3.000.000, 8h => 5.000.000
    //     $base = 0;
    //     if ($minutes <= 240) {
    //         $base = (int) ($fee->four_hour_shift_fee ?? 0);
    //     } else {
    //         $base = (int) ($fee->eight_hour_shift_fee ?? 0);
    //     }

    //     return (int) round($base * $multiplier);
    // }

    private static function overlaps(Carbon $aStart, Carbon $aEnd, Carbon $bStart, Carbon $bEnd): bool
    {
        return $aStart->lessThan($bEnd) && $aEnd->greaterThan($bStart);
    }

    private static function empty(): array
    {
        return [
            'gathering' => 0,
            'lifting' => 0,
            'total' => 0,
            'meta' => [],
        ];
    }
}
