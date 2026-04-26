<?php

namespace App\Services;

use App\Models\RegisterDirectly;

class FeeCalculator
{
    public static function durationMinutes($entryTime, $exitTime)
    {
        $entry = \Carbon\Carbon::parse($entryTime);
        $exit = \Carbon\Carbon::parse($exitTime);

        if ($exit->lessThan($entry)) {
            return 0;
        }

        return (int) $exit->diffInMinutes($entry);
    }

    private function calculateHours($entryTime, $exitTime)
    {
        $entry = \Carbon\Carbon::parse($entryTime);
        $exit = \Carbon\Carbon::parse($exitTime);

        return $exit->diffInHours($entry);
    }

    public static function formatDurationForDisplay($entryTime, $exitTime)
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

    private function calculateFee(RegisterDirectly $record)
    {
        // Tính tổng số phút từ actual_date_in đến actual_date_out (hoặc now nếu chưa ra)
        $exitTime = $record->actual_date_out ?? now();
        $totalMinutes = self::durationMinutes($record->actual_date_in, $exitTime);

        // Ưu tiên fee_id trực tiếp trên record
        $fee = $record->fee;

        // Nếu không có, lấy thông tin fee từ registration vehicle
        if (! $fee && $record->registrationVehicle && $record->registrationVehicle->fee_id) {
            $fee = \App\Models\Fee::find($record->registrationVehicle->fee_id);
        }

        // Nếu vẫn không có fee, dùng giá mặc định
        if (! $fee) {
            // Tìm fee mặc định cho xe nhỏ (ID: 1)
            $fee = \App\Models\Fee::find(1); // Xe 3 bánh, Xe ô tô đến 9 chỗ, xe tải dưới 1,5 tấn và xe bán tải
        }

        // Fallback nếu vẫn không có fee
        if (! $fee) {
            return 50000; // Phí cố định
        }

        $baseFee = $fee->base_fee_120min;
        $additionalFee = $fee->additional_fee_30min;

        if ($totalMinutes <= 120) {
            // ≤ 120 phút: fee = base_fee_120min
            return $baseFee;
        } else {
            // > 120 phút: fee = (base_fee_120min + additional_fee_30min × số_block)
            $extraMinutes = $totalMinutes - 120;
            $extraBlocks = ceil($extraMinutes / 30); // Mỗi 30 phút hoặc phần dư tính 1 block

            return $baseFee + ($additionalFee * $extraBlocks);
        }
    }

    // Public method để gọi từ bên ngoài
    public function calculateFeePublic(RegisterDirectly $record)
    {
        return $this->calculateFee($record);
    }

    public static function forRegistrationEntry(RegisterDirectly $record)
    {
        $calculator = new self();
        $total = $calculator->calculateFee($record);

        return [
            'total' => $total,
        ];
    }
}
