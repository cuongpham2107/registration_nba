<?php

namespace Database\Seeders;

use App\Models\GatheringPointFee;
use App\Models\LiftingServiceFee;
use App\Models\VisitorVehicleFee;
use Illuminate\Database\Seeder;

class FeeTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Biểu phí phương tiện ra vào làm việc (visitor_registrations)
        $visitorFees = [
            [
                'vehicle_type' => 'Xe đạp, xe đạp điện, xe máy, xe máy điện',
                'per_visit_fee' => 5000,
                'monthly_fee' => 150000,
                'is_active' => true,
            ],
            [
                'vehicle_type' => 'Xe ô tô đến 9 chỗ, xe tải đến 1.5 tấn, xe 3 bánh và xe bán tải',
                'per_visit_fee' => 15000,
                'monthly_fee' => 600000,
                'is_active' => true,
            ],
            [
                'vehicle_type' => 'Xe ô tô 10-26 chỗ, xe tải lớn hơn 1.5 tấn đến 3.5 tấn',
                'per_visit_fee' => 20000,
                'monthly_fee' => 800000,
                'is_active' => true,
            ],
            [
                'vehicle_type' => 'Xe ô tô từ 17-27 chỗ, xe tải lớn hơn 3.5 tấn đến 7 tấn',
                'per_visit_fee' => 30000,
                'monthly_fee' => 1000000,
                'is_active' => true,
            ],
            [
                'vehicle_type' => 'Xe ô tô từ 30 chỗ trở lên, xe tải trên 7 tấn, xe container, xe kéo rơ moóc',
                'per_visit_fee' => 40000,
                'monthly_fee' => 1200000,
                'is_active' => true,
            ],
        ];

        foreach ($visitorFees as $fee) {
            VisitorVehicleFee::create($fee);
        }

        // 2. Biểu phí địa điểm tập trung (vehicle_registrations)
        $gatheringPointFees = [
            [
                'vehicle_type' => 'Xe ô tô đến 9 chỗ, xe tải đến 1.5 tấn',
                'morning_fee' => 30000,
                'afternoon_fee' => 30000,
                'full_day_fee' => 60000,
                'night_fee' => 90000,
                'is_active' => true,
            ],
            [
                'vehicle_type' => 'Xe ô tô 10-26 chỗ, xe tải lớn hơn 1.5 tấn đến 3.5 tấn',
                'morning_fee' => 41000,
                'afternoon_fee' => 41000,
                'full_day_fee' => 82000,
                'night_fee' => 123000,
                'is_active' => true,
            ],
            [
                'vehicle_type' => 'Xe ô tô từ 17-27 chỗ, xe tải lớn hơn 3.5 tấn đến 7 tấn',
                'morning_fee' => 60000,
                'afternoon_fee' => 60000,
                'full_day_fee' => 120000,
                'night_fee' => 180000,
                'is_active' => true,
            ],
            [
                'vehicle_type' => 'Xe ô tô từ 30 chỗ trở lên, xe tải lớn hơn 7 tấn đến 10 tấn, xe chở container 20"',
                'morning_fee' => 85000,
                'afternoon_fee' => 85000,
                'full_day_fee' => 170000,
                'night_fee' => 255000,
                'is_active' => true,
            ],
            [
                'vehicle_type' => 'Xe chở container 40" - 45", xe tải trên 10 tấn, xe moóc',
                'morning_fee' => 110000,
                'afternoon_fee' => 110000,
                'full_day_fee' => 220000,
                'night_fee' => 330000,
                'is_active' => true,
            ],
        ];

        foreach ($gatheringPointFees as $fee) {
            GatheringPointFee::create($fee);
        }

        // 3. Biểu phí dịch vụ nâng hạ (vehicle_registrations)
        $liftingServiceFees = [
            // Dưới 2 tấn
            [
                'service_name' => 'Kiện hàng dưới 1 tấn, kích thước dài * rộng dưới 1.5m',
                'weight_category' => 'under_2_tons',
                'regular_hours_fee' => 80000,
                'four_hour_shift_fee' => null,
                'eight_hour_shift_fee' => null,
                'after_hours_fee' => 150, // 150%
                'is_active' => true,
            ],
            [
                'service_name' => 'Kiện hàng trên 1 tấn đến dưới 2 tấn, kích thước dài*rộng dưới 2m',
                'weight_category' => 'under_2_tons',
                'regular_hours_fee' => 120000,
                'four_hour_shift_fee' => null,
                'eight_hour_shift_fee' => null,
                'after_hours_fee' => 150, // 150%
                'is_active' => true,
            ],
            // Trên 2 tấn
            [
                'service_name' => 'Thuê xe nâng 5 tấn',
                'weight_category' => 'over_2_tons',
                'regular_hours_fee' => null,
                'four_hour_shift_fee' => 3000000,
                'eight_hour_shift_fee' => 5000000,
                'after_hours_fee' => 150, // 150%
                'is_active' => true,
            ],
            [
                'service_name' => 'Thuê xe nâng 10 tấn',
                'weight_category' => 'over_2_tons',
                'regular_hours_fee' => null,
                'four_hour_shift_fee' => 4500000,
                'eight_hour_shift_fee' => 8000000,
                'after_hours_fee' => 150, // 150%
                'is_active' => true,
            ],
            [
                'service_name' => 'Thuê xe nâng 15 tấn',
                'weight_category' => 'over_2_tons',
                'regular_hours_fee' => null,
                'four_hour_shift_fee' => 7000000,
                'eight_hour_shift_fee' => 12000000,
                'after_hours_fee' => 150, // 150%
                'is_active' => true,
            ],
            [
                'service_name' => 'Cẩu 50 tấn',
                'weight_category' => 'over_2_tons',
                'regular_hours_fee' => null,
                'four_hour_shift_fee' => 5500000,
                'eight_hour_shift_fee' => 10000000,
                'after_hours_fee' => 150, // 150%
                'is_active' => true,
            ],
        ];

        foreach ($liftingServiceFees as $fee) {
            LiftingServiceFee::create($fee);
        }

        $this->command->info('Đã seed dữ liệu mẫu cho 3 bảng phí!');
    }
}
