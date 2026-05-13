<?php

namespace App\Support;

use App\Models\Fee;
use App\Models\RegistrationEntry;
use Carbon\Carbon;

class FeeCalculator
{
    /**
     * Format duration for display (same rules as invoices):
     * - If < 60 minutes: show "N phút"
     * - If >= 60 minutes: show "H giờ, M phút"
     */
    public static function formatDurationForDisplay($entryTime, $exitTime, string $timezone = 'Asia/Ho_Chi_Minh'): string
    {
        if (blank($entryTime) || blank($exitTime)) {
            return '0 phút';
        }

        $entry = Carbon::parse($entryTime, $timezone);
        $exit = Carbon::parse($exitTime, $timezone);

        if ($exit->lessThan($entry)) {
            [$entry, $exit] = [$exit, $entry];
        }

        // Làm tròn lên theo phút để hiển thị (vd: 27.35 phút => 28 phút).
        // Carbon diffInMinutes mặc định trả về số nguyên, nhưng vẫn ceil để thống nhất rule hiển thị.
        $totalMinutes = (int) ceil($entry->diffInSeconds($exit) / 60);

        if ($totalMinutes < 60) {
            return $totalMinutes.' phút';
        }

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return $hours.' giờ, '.$minutes.' phút';
    }

    /**
     * Total minutes between entry & exit (always non-negative).
     */
    public static function durationMinutes($entryTime, $exitTime, string $timezone = 'Asia/Ho_Chi_Minh'): int
    {
        if (blank($entryTime) || blank($exitTime)) {
            return 0;
        }

        $entry = Carbon::parse($entryTime, $timezone);
        $exit = Carbon::parse($exitTime, $timezone);

        if ($exit->lessThan($entry)) {
            [$entry, $exit] = [$exit, $entry];
        }

        return (int) ceil($entry->diffInSeconds($exit) / 60);
    }

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
        // NOTE: diffInMinutes() sẽ trả 0 nếu < 60s. Nên ép tối thiểu 1 phút khi exit <= entry.
        if ($exit->lessThanOrEqualTo($entry)) {
            $exit = $entry->copy()->addMinute();
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
        $dayFee = (int) ($fee->full_day_fee ?? 0);
        $nightFee = (int) ($fee->night_fee ?? 0);
        $total = 0;

        $cursor = $entry->copy();
        while ($cursor->lessThan($exit)) {
            // Điểm kết thúc của block này (tối đa 4h hoặc dừng tại thời điểm ra)
            $blockEnd = $cursor->copy()->addHours(4);
            if ($blockEnd->greaterThan($exit)) {
                $blockEnd = $exit->copy();
            }

            // Xác định giá của block dựa trên giờ kết thúc của nó
            $hour = (int) $blockEnd->format('G'); // 0-23
            $isNight = $hour < 7 || $hour >= 17;

            $total += $isNight ? $nightFee : $dayFee;

            // Nhảy sang block tiếp theo
            $cursor->addHours(4);
        }

        return $total;
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
