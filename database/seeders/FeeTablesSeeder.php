<?php

namespace Database\Seeders;

use App\Models\Fee;
use Illuminate\Database\Seeder;

class FeeTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Biểu phí phương tiện ra vào làm việc (visitor_registrations)
        $fees = [
            [
                'vehicle_type' => 'Xe đạp, xe đạp điện, xe máy, xe máy điện',
                'full_day_fee' => 5000,
                'night_fee' => 0,
                'is_active' => true,
            ],
            [
                'vehicle_type' => 'Xe ô tô dưới 29 chỗ, xe tải dưới 5 tấn',
                'full_day_fee' => 60000,
                'night_fee' => 90000,
                'is_active' => true,
            ],
            [
                'vehicle_type' => 'Xe ô tô từ 30 chỗ trở lên, xe tải lớn hơn 5 tấn đến 10 tấn',
                'full_day_fee' => 90000,
                'night_fee' => 135000,
                'is_active' => true,
            ],
            [
                'vehicle_type' => 'Xe tải lớn trên 10 tấn, xe container, xe kéo rơ moóc',
                'full_day_fee' => 120000,
                'night_fee' => 180000,
                'is_active' => true,
            ],
        ];

        foreach ($fees as $fee) {
            Fee::create($fee);
        }

        $this->command->info('Đã seed dữ liệu mẫu cho bảng phí!');
    }
}
