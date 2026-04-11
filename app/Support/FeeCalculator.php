<?php

namespace App\Support;

use App\Models\Fee;
use App\Models\RegistrationEntry;
use Carbon\Carbon;

class FeeCalculator
{
    public static function forRegistrationEntry(RegistrationEntry $entry): array
    {
        // Single pricing table (`fees`) for all entry types.
        $entryTime = $entry->actual_date_in;
        $exitTime = $entry->actual_date_out ?? now();

        $guest = $entry->guest;
        if (! $guest) {
            return self::empty();
        }

        $fee = $guest->fee;

        $feeAmount = self::calculateFee(
            fee: $fee,
            entryTime: $entryTime,
            exitTime: $exitTime,
        );

        return [
            'fee' => $feeAmount,
            'total' => $feeAmount,
            'meta' => [
                'entry_time' => $entryTime,
                'exit_time' => $exitTime,
            ],
        ];
    }

    public static function calculateFee(?Fee $fee, $entryTime, $exitTime): int
    {
        if (! $fee || blank($entryTime) || blank($exitTime)) {
            return 0;
        }

        $entry = Carbon::parse($entryTime);
        $exit = Carbon::parse($exitTime);
        if ($exit->lessThan($entry)) {
            [$entry, $exit] = [$exit, $entry];
        }

        // Business rule: nếu đã có giờ vào/ra hợp lệ thì tối thiểu tính 1 block.
        // Tránh trường hợp vào/ra cùng timestamp (0 phút) nhưng vẫn cần thu phí.
        if ($exit->equalTo($entry)) {
            $exit = $exit->copy()->addMinute();
        }

        // New rules (per yêu cầu):
        // - Chỉ dùng 1 bảng `fees`.
        // - Nếu là xe máy: tính 1 giá duy nhất (full_day_fee).
        // - Các loại còn lại: chia ca theo block 4h.
        //     + 07:00 -> 17:00: tính theo full_day_fee * số block(4h)
        //     + 17:00 -> 07:00 hôm sau: tính theo night_fee * số block(4h)
        // - Số block = ceil(số phút sử dụng trong ca / 240)

        $vehicleType = mb_strtolower((string) ($fee->vehicle_type ?? ''), 'UTF-8');
        $isMotorbike = str_contains($vehicleType, 'xe máy') || str_contains($vehicleType, 'xe may');
        if ($isMotorbike) {
            return (int) ($fee->full_day_fee ?? 0);
        }

        return self::calculateShiftBlocks(
            fee: $fee,
            entry: $entry,
            exit: $exit,
        );
    }

    private static function calculateShiftBlocks(Fee $fee, Carbon $entry, Carbon $exit): int
    {
        $total = 0;

        // Iterate day by day, summing overlap minutes with day shift and night shift.
        // Day shift: 07:00 -> 17:00
        // Night shift: 17:00 -> 07:00 next day
        $cursorDay = $entry->copy()->startOfDay();
        $endDay = $exit->copy()->startOfDay();

        while ($cursorDay->lessThanOrEqualTo($endDay)) {
            $dayStart = $cursorDay->copy()->setTime(7, 0);
            $dayEnd = $cursorDay->copy()->setTime(17, 0);
            $nightStart = $dayEnd->copy();
            $nightEnd = $cursorDay->copy()->addDay()->setTime(7, 0);

            $dayMinutes = self::overlapMinutes($entry, $exit, $dayStart, $dayEnd);
            if ($dayMinutes > 0) {
                $blocks = (int) ceil($dayMinutes / 240);
                $total += $blocks * (int) ($fee->full_day_fee ?? 0);
            }

            $nightMinutes = self::overlapMinutes($entry, $exit, $nightStart, $nightEnd);
            if ($nightMinutes > 0) {
                $blocks = (int) ceil($nightMinutes / 240);
                $total += $blocks * (int) ($fee->night_fee ?? 0);
            }

            $cursorDay->addDay();
        }

        return $total;
    }

    private static function overlapMinutes(Carbon $aStart, Carbon $aEnd, Carbon $bStart, Carbon $bEnd): int
    {
        if (! self::overlaps($aStart, $aEnd, $bStart, $bEnd)) {
            return 0;
        }

        $start = $aStart->copy()->max($bStart);
        $end = $aEnd->copy()->min($bEnd);

        return max(0, $start->diffInMinutes($end));
    }

    private static function overlaps(Carbon $aStart, Carbon $aEnd, Carbon $bStart, Carbon $bEnd): bool
    {
        return $aStart->lessThan($bEnd) && $aEnd->greaterThan($bStart);
    }

    private static function empty(): array
    {
        return [
            'fee' => 0,
            'total' => 0,
            'meta' => [],
        ];
    }
}
