<?php

namespace App\Support;

use App\Models\GatheringPointFee;
use App\Models\RegistrationEntry;
use App\Models\VisitorVehicleFee;
use Carbon\Carbon;

class FeeCalculator
{
    public static function forRegistrationEntry(RegistrationEntry $entry): array
    {
        return match ($entry->type) {
            'inspection' => self::forRegistrationInspection($entry),
            default => self::forRegistrationWorking($entry),
        };
    }

    /**
     * Contract:
     * - Input: RegistrationEntry (expects actual_date_in, actual_date_out? and guest relations)
     * - Output: array{gathering:int,total:int,meta:array}
     */
    public static function forRegistrationInspection(RegistrationEntry $entry): array
    {
        $entryTime = $entry->actual_date_in;
        $exitTime = $entry->actual_date_out ?? now();

        $guest = $entry->guest;
        if (! $guest) {
            return self::empty();
        }

        $gatheringFee = $guest->gatheringPointFee;

        $gatheringAmount = self::calculateGathering(
            fee: $gatheringFee,
            entryTime: $entryTime,
            exitTime: $exitTime,
        );

        return [
            'gathering' => $gatheringAmount,
            'total' => $gatheringAmount,
            'meta' => [
                'entry_time' => $entryTime,
                'exit_time' => $exitTime,
            ],
        ];
    }

    public static function forRegistrationWorking(RegistrationEntry $entry): array
    {
        $entryTime = $entry->actual_date_in;
        $exitTime = $entry->actual_date_out ?? now();

        $guest = $entry->guest;
        if (! $guest) {
            return self::empty();
        }

        $workingFee = $guest->visitorVehicleFee;

        $workingAmount = self::calculateVisitorVehicle(
            fee: $workingFee,
        );

        return [
            'working' => $workingAmount,
            'total' => $workingAmount,
            'meta' => [
                'entry_time' => $entryTime,
                'exit_time' => $exitTime,
            ],
        ];
    }

    public static function calculateVisitorVehicle(?VisitorVehicleFee $fee): int
    {

        // Rule (per yêu cầu):
        // - per_visit_fee = phí cho 1 lượt ra/vào
        // - monthly_fee là vé tháng nhưng hiện KHÔNG dùng trong tính phí
        return (int) ($fee->per_visit_fee ?? 0);
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

    private static function overlaps(Carbon $aStart, Carbon $aEnd, Carbon $bStart, Carbon $bEnd): bool
    {
        return $aStart->lessThan($bEnd) && $aEnd->greaterThan($bStart);
    }

    private static function empty(): array
    {
        return [
            'gathering' => 0,
            'total' => 0,
            'meta' => [],
        ];
    }
}
