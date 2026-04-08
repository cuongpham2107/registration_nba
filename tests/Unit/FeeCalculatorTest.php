<?php

namespace Tests\Unit;

use App\Models\VisitorVehicleFee;
use App\Support\FeeCalculator;
use Carbon\Carbon;
use Tests\TestCase;

class FeeCalculatorTest extends TestCase
{
    public function test_calculate_visitor_vehicle_returns_zero_when_missing_inputs(): void
    {
        $fee = new VisitorVehicleFee([
            'per_visit_fee' => 5000,
            'monthly_fee' => 150000,
        ]);

        $this->assertSame(0, FeeCalculator::calculateVisitorVehicle($fee, null, now()));
        $this->assertSame(0, FeeCalculator::calculateVisitorVehicle(null, now(), now()));
    }

    public function test_calculate_visitor_vehicle_uses_monthly_fee_within_same_month(): void
    {
        $fee = new VisitorVehicleFee([
            'per_visit_fee' => 5000,
            'monthly_fee' => 150000,
        ]);

        $entry = Carbon::create(2026, 4, 8, 8, 0, 0);
        $exit = Carbon::create(2026, 4, 8, 17, 0, 0);

        $this->assertSame(5000, FeeCalculator::calculateVisitorVehicle($fee, $entry, $exit));
    }

    public function test_calculate_visitor_vehicle_multiplies_monthly_when_crossing_month_boundary(): void
    {
        $fee = new VisitorVehicleFee([
            'per_visit_fee' => 5000,
            'monthly_fee' => 150000,
        ]);

        $entry = Carbon::create(2026, 4, 30, 23, 0, 0);
        $exit = Carbon::create(2026, 5, 1, 1, 0, 0);

        $this->assertSame(5000, FeeCalculator::calculateVisitorVehicle($fee, $entry, $exit));
    }

    public function test_calculate_visitor_vehicle_falls_back_to_per_visit_when_monthly_is_zero(): void
    {
        $fee = new VisitorVehicleFee([
            'per_visit_fee' => 5000,
            'monthly_fee' => 0,
        ]);

        $entry = Carbon::create(2026, 4, 8, 8, 0, 0);
        $exit = Carbon::create(2026, 4, 8, 17, 0, 0);

        $this->assertSame(5000, FeeCalculator::calculateVisitorVehicle($fee, $entry, $exit));
    }
}
