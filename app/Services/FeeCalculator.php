<?php

namespace App\Services;

use App\Models\RegisterDirectly;

class FeeCalculator
{
    public static function durationMinutes($entryTime, $exitTime): int
    {
        $entry = \Carbon\Carbon::parse($entryTime);
        $exit = \Carbon\Carbon::parse($exitTime);

        if ($exit->lessThan($entry)) {
            return 0;
        }

        return (int) $exit->diffInMinutes($entry);
    }

    public static function formatDurationForDisplay($entryTime, $exitTime): string
    {
        $entry = \Carbon\Carbon::parse($entryTime);
        $exit = \Carbon\Carbon::parse($exitTime);
        $diff = $exit->diff($entry);

        $parts = [];
        if ($diff->d > 0) {
            $parts[] = $diff->d.' ngày';
        }
        if ($diff->h > 0) {
            $parts[] = $diff->h.' giờ';
        }
        if ($diff->i > 0) {
            $parts[] = $diff->i.' phút';
        }

        return empty($parts) ? '0 phút' : implode(' ', $parts);
    }

    public static function calculateFee(RegisterDirectly $record): int
    {
        $exitTime = $record->actual_date_out ?? now();
        $totalMinutes = self::durationMinutes($record->actual_date_in, $exitTime);

        $fee = $record->fee;

        if (! $fee && $record->registrationVehicle && $record->registrationVehicle->fee_id) {
            $fee = \App\Models\Fee::find($record->registrationVehicle->fee_id);
        }

        if (! $fee) {
            $fee = \App\Models\Fee::find(1);
        }

        if (! $fee) {
            return 50000;
        }

        $baseFee = $fee->base_fee_120min;
        $additionalFee = $fee->additional_fee_30min;

        if ($totalMinutes <= 120) {
            return $baseFee;
        }

        $extraMinutes = $totalMinutes - 120;
        $extraBlocks = (int) ceil($extraMinutes / 30);

        return $baseFee + ($additionalFee * $extraBlocks);
    }

    public static function forRegistrationEntry(RegisterDirectly $record): int
    {
        return self::calculateFee($record);
    }
}
