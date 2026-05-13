<?php

namespace Tests\Unit\Support;

use App\Models\Fee;
use App\Support\FeeCalculator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for FeeCalculator::calculateFee.
 *
 * Business rules:
 *  - Motorbike: always 1 flat fee (full_day_fee), ignoring duration.
 *  - Car / other:
 *      ≤ 4h total → 1 block only, shift determined by EXIT hour:
 *          07:00 <= exit < 17:00 → day fee
 *          exit >= 17:00 OR exit < 07:00 → night fee
 *      > 4h total → split into day/night shifts, blocks = ceil(minutes_in_shift / 240).
 */
class FeeCalculatorTest extends TestCase
{
    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    /**
     * Build a Fee model stub without hitting the database.
     *
     * @param  array{vehicle_type?: string, full_day_fee?: int, night_fee?: int}  $attributes
     */
    private function makeFee(array $attributes = []): Fee
    {
        $fee = new Fee;
        $fee->vehicle_type = $attributes['vehicle_type'] ?? 'Xe ô tô dưới 29 chỗ, xe tải dưới 5 tấn';
        $fee->full_day_fee = $attributes['full_day_fee'] ?? 60000;
        $fee->night_fee = $attributes['night_fee'] ?? 90000;

        return $fee;
    }

    // -----------------------------------------------------------------
    // Guard cases
    // -----------------------------------------------------------------

    public function test_returns_zero_when_fee_is_null(): void
    {
        $result = FeeCalculator::calculateFee(null, '2026-05-04 08:00:00', '2026-05-04 10:00:00');
        $this->assertSame(0, $result);
    }

    public function test_returns_zero_when_entry_is_blank(): void
    {
        $result = FeeCalculator::calculateFee($this->makeFee(), null, '2026-05-04 10:00:00');
        $this->assertSame(0, $result);
    }

    public function test_returns_zero_when_exit_is_blank(): void
    {
        $result = FeeCalculator::calculateFee($this->makeFee(), '2026-05-04 08:00:00', null);
        $this->assertSame(0, $result);
    }

    // -----------------------------------------------------------------
    // Motorbike: flat fee regardless of duration
    // -----------------------------------------------------------------

    public function test_motorbike_returns_flat_fee(): void
    {
        $fee = $this->makeFee(['vehicle_type' => 'Xe máy', 'full_day_fee' => 5000]);
        $result = FeeCalculator::calculateFee($fee, '2026-05-04 08:00:00', '2026-05-04 20:00:00');
        $this->assertSame(5000, $result);
    }

    public function test_motorbike_alias_xe_may_returns_flat_fee(): void
    {
        $fee = $this->makeFee(['vehicle_type' => 'xe may', 'full_day_fee' => 5000]);
        $result = FeeCalculator::calculateFee($fee, '2026-05-04 08:00:00', '2026-05-04 10:00:00');
        $this->assertSame(5000, $result);
    }

    // -----------------------------------------------------------------
    // ≤ 4h: single block, shift by exit hour
    // -----------------------------------------------------------------

    /**
     * Bản ghi thực: 29H-95052
     * Vào 16:33:50, Ra 17:29:09 → 56 phút (< 4h).
     * Giờ ra 17:29 >= 17:00 → ca đêm → 1 block × 90k = 90.000đ.
     */
    public function test_real_record_29h95052_charges_night_fee(): void
    {
        $fee = $this->makeFee(['full_day_fee' => 60000, 'night_fee' => 90000]);
        $result = FeeCalculator::calculateFee($fee, '2026-05-04 16:33:50', '2026-05-04 17:29:09');
        $this->assertSame(90000, $result, '29H-95052: 56 phút, ra 17:29 → ca đêm → 90.000đ');
    }

    /** Vào 10:00, Ra 11:00 (1h). Giờ ra 11:00 → ca ngày → 60k. */
    public function test_under_4h_day_exit_charges_day_fee(): void
    {
        $fee = $this->makeFee();
        $result = FeeCalculator::calculateFee($fee, '2026-05-04 10:00:00', '2026-05-04 11:00:00');
        $this->assertSame(60000, $result);
    }

    /** Vào 16:00, Ra 17:00 (1h). Giờ ra đúng 17:00 → ca đêm → 90k. */
    public function test_under_4h_exit_at_17_is_night(): void
    {
        $fee = $this->makeFee();
        $result = FeeCalculator::calculateFee($fee, '2026-05-04 16:00:00', '2026-05-04 17:00:00');
        $this->assertSame(90000, $result);
    }

    /** Vào 16:00, Ra 19:00 (3h). Giờ ra 19:00 → ca đêm → 90k. */
    public function test_under_4h_spanning_shift_uses_exit_shift(): void
    {
        $fee = $this->makeFee();
        $result = FeeCalculator::calculateFee($fee, '2026-05-04 16:00:00', '2026-05-04 19:00:00');
        $this->assertSame(90000, $result, 'Vắt 2 ca nhưng < 4h → chỉ tính theo giờ ra (ca đêm)');
    }

    /** Vào 6:00, Ra 8:00 (2h). Giờ ra 8:00 → ca ngày → 60k. */
    public function test_under_4h_spanning_midnight_shift_uses_exit_shift(): void
    {
        $fee = $this->makeFee();
        $result = FeeCalculator::calculateFee($fee, '2026-05-04 06:00:00', '2026-05-04 08:00:00');
        $this->assertSame(60000, $result, 'Vắt đêm/sáng nhưng < 4h → giờ ra 8:00 → ca ngày');
    }

    /** Vào 5:00, Ra 6:30 (90 phút). Giờ ra 6:30 < 7:00 → ca đêm → 90k. */
    public function test_under_4h_early_morning_exit_before_7_is_night(): void
    {
        $fee = $this->makeFee();
        $result = FeeCalculator::calculateFee($fee, '2026-05-04 05:00:00', '2026-05-04 06:30:00');
        $this->assertSame(90000, $result, 'Ra 6:30 < 07:00 → ca đêm');
    }

    /** Đúng 4h (240 phút) → vẫn thuộc nhánh ≤ 4h → 1 block theo giờ ra. */
    public function test_exactly_4h_is_single_block(): void
    {
        $fee = $this->makeFee();
        // Vào 8:00, ra 12:00 (4h đúng). Giờ ra 12:00 → ca ngày.
        $result = FeeCalculator::calculateFee($fee, '2026-05-04 08:00:00', '2026-05-04 12:00:00');
        $this->assertSame(60000, $result, '4h đúng → 1 block ca ngày');
    }

    // -----------------------------------------------------------------
    // > 4h: block calculation per shift
    // -----------------------------------------------------------------

    /**
     * Vào 08:00, Ra 14:00 (6h, hoàn toàn trong ca ngày 07-17).
     * Ca ngày: 360 phút → ceil(360/240) = 2 block × 60k = 120k.
     */
    public function test_over_4h_full_day_shift_two_blocks(): void
    {
        $fee = $this->makeFee();
        $result = FeeCalculator::calculateFee($fee, '2026-05-04 08:00:00', '2026-05-04 14:00:00');
        $this->assertSame(120000, $result, '6h trong ca ngày → 2 block × 60k = 120k');
    }

    /**
     * Vào 16:30, Ra 21:00 (270 phút).
     * Block 1: 16:30 -> 20:30 (Kết thúc 20:30 - Đêm) -> 90k
     * Block 2: 20:30 -> 21:00 (Kết thúc 21:00 - Đêm) -> 90k
     * Tổng: 180k.
     */
    public function test_over_4h_spanning_day_night_shift(): void
    {
        $fee = $this->makeFee();
        $result = FeeCalculator::calculateFee($fee, '2026-05-04 16:30:00', '2026-05-04 21:00:00');
        $this->assertSame(180000, $result);
    }

    /**
     * Vào 16:00, Ra 22:30 (390 phút).
     * Block 1: 16:00 -> 20:00 (Kết thúc 20:00 - Đêm) -> 90k
     * Block 2: 20:00 -> 22:30 (Kết thúc 22:30 - Đêm) -> 90k
     * Tổng: 180k.
     */
    public function test_over_4h_two_night_blocks(): void
    {
        $fee = $this->makeFee();
        $result = FeeCalculator::calculateFee($fee, '2026-05-04 16:00:00', '2026-05-04 22:30:00');
        $this->assertSame(180000, $result);
    }

    /**
     * Vào 17:00, Ra 01:30 hôm sau (510 phút).
     * Block 1: 17:00-21:00 (Đêm) -> 90k
     * Block 2: 21:00-01:00 (Đêm) -> 90k
     * Block 3: 01:00-01:30 (Đêm) -> 90k
     * Tổng: 270k.
     */
    public function test_over_4h_full_night_shift_three_blocks(): void
    {
        $fee = $this->makeFee();
        $result = FeeCalculator::calculateFee($fee, '2026-05-04 17:00:00', '2026-05-05 01:30:00');
        $this->assertSame(270000, $result);
    }

    /**
     * Case: Gửi 24 tiếng (Vào 08:00 hôm trước, Ra 08:00 hôm sau)
     * Block 1: 08-12 (Ngày) -> 60k
     * Block 2: 12-16 (Ngày) -> 60k
     * Block 3: 16-20 (Đêm) -> 90k
     * Block 4: 20-00 (Đêm) -> 90k
     * Block 5: 00-04 (Đêm) -> 90k
     * Block 6: 04-08 (Ngày) -> 60k
     * Tổng: 60*3 + 90*3 = 180 + 270 = 450k.
     */
    public function test_full_24_hours_overnight(): void
    {
        $fee = $this->makeFee();
        $result = FeeCalculator::calculateFee($fee, '2026-05-04 08:00:00', '2026-05-05 08:00:00');
        $this->assertSame(450000, $result);
    }
}
