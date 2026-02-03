<?php

require_once 'vendor/autoload.php';

// Test với data thực tế từ PDF
$entryTime = '2025-12-04 11:02:00';
$exitTime = '2026-02-03 10:46:00';

$entry = new DateTime($entryTime);
$exit = new DateTime($exitTime);

$totalMinutes = $exit->getTimestamp()/60 - $entry->getTimestamp()/60;
$totalHours = floor($totalMinutes / 60);
$remainingMinutes = $totalMinutes % 60;

echo "=== Thông tin thời gian ===\n";
echo "Vào: " . $entry->format('d/m/Y H:i') . "\n";
echo "Ra: " . $exit->format('d/m/Y H:i') . "\n";
echo "Tổng: $totalHours giờ / $remainingMinutes phút\n";
echo "Tổng phút: $totalMinutes phút\n\n";

// Test tính phí với price list ID: 1
$baseFee = 15000;      // base_fee_120min
$additionalFee = 5000; // additional_fee_30min

echo "=== Tính phí với Price List ID: 1 ===\n";
echo "Base fee (120 phút đầu): " . number_format($baseFee) . " VND\n";
echo "Additional fee (mỗi 30 phút): " . number_format($additionalFee) . " VND\n\n";

if ($totalMinutes <= 120) {
    $fee = $baseFee;
    echo "≤ 120 phút → Phí: " . number_format($fee) . " VND\n";
} else {
    $extraMinutes = $totalMinutes - 120;
    $extraBlocks = ceil($extraMinutes / 30);
    $fee = $baseFee + ($additionalFee * $extraBlocks);
    
    echo "Phút thêm: " . number_format($extraMinutes) . "\n";
    echo "Blocks thêm: " . number_format($extraBlocks) . "\n";
    echo "Phí cuối: " . number_format($fee) . " VND\n";
}

echo "\n=== So sánh với PDF ===\n";
echo "PDF hiển thị: 50.000 VND\n";
echo "Logic tính ra: " . number_format($fee) . " VND\n";
echo "Chênh lệch: " . number_format($fee - 50000) . " VND\n";
