<?php

// Test fee calculation logic theo công thức mới
function calculateFeeNew($minutes, $baseFee = 20000, $additionalFee = 5000)
{
    if ($minutes <= 120) {
        // ≤ 120 phút: fee = base_fee_120min
        return $baseFee;
    } else {
        // > 120 phút: fee = (base_fee_120min + additional_fee_30min × số_block)
        $extraMinutes = $minutes - 120;
        $extraBlocks = ceil($extraMinutes / 30);
        
        return $baseFee + ($additionalFee * $extraBlocks);
    }
}

echo "=== Test Fee Calculation - Công thức mới ===\n";
echo "base_fee_120min = 20,000 VND\n";
echo "additional_fee_30min = 5,000 VND\n\n";

// Test cases
$testCases = [60, 120, 121, 150, 151, 180, 181, 210, 211, 240, 383];

foreach ($testCases as $minutes) {
    $fee = calculateFeeNew($minutes);
    $hours = floor($minutes / 60);
    $remainingMinutes = $minutes % 60;
    
    echo sprintf(
        "%d phút (%dh%02dm) → %s VND", 
        $minutes, 
        $hours, 
        $remainingMinutes, 
        number_format($fee)
    );
    
    if ($minutes > 120) {
        $extraMinutes = $minutes - 120;
        $extraBlocks = ceil($extraMinutes / 30);
        echo sprintf(" (base + %d blocks)", $extraBlocks);
    }
    echo "\n";
}

echo "\n=== Logic Explanation ===\n";
echo "- ≤ 120 phút: fee = base_fee_120min\n";
echo "- > 120 phút: fee = base_fee_120min + (additional_fee_30min × blocks)\n";
echo "- blocks = ceil((minutes - 120) / 30)\n";


// === Test Fee Calculation ===
// base_fee_120min = 40,000 VND
// additional_fee_30min = 15,000 VND

// 60 phút (1h00m) → 40,000 VND
// 120 phút (2h00m) → 40,000 VND
// 121 phút (2h01m) → 55,000 VND (base + 1 blocks)
// 150 phút (2h30m) → 55,000 VND (base + 1 blocks)
// 151 phút (2h31m) → 70,000 VND (base + 2 blocks)
// 180 phút (3h00m) → 70,000 VND (base + 2 blocks)
// 181 phút (3h01m) → 85,000 VND (base + 3 blocks)
// 210 phút (3h30m) → 85,000 VND (base + 3 blocks)
// 211 phút (3h31m) → 100,000 VND (base + 4 blocks)
// 240 phút (4h00m) → 100,000 VND (base + 4 blocks)

// === Logic Explanation ===
// - ≤ 120 phút: fee = base_fee_120min
// - > 120 phút: fee = base_fee_120min + (additional_fee_30min × blocks)
// - blocks = ceil((minutes - 120) / 30)