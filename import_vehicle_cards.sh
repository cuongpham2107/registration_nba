#!/bin/bash
set -e

# Đường dẫn đến file CSV (mặc định)
CSV_FILE="${1:-Book 1(Sheet1)-3.csv}"

echo "=== Import Vehicle Cards ==="
echo "File: $CSV_FILE"

# Chạy migration
echo ""
echo ">>> Running migration..."
php artisan migrate --path=database/migrations/2026_06_11_000001_create_vehicle_cards_table.php --force

# Import dữ liệu
echo ""
echo ">>> Importing CSV data..."
php artisan app:import-vehicle-cards "$CSV_FILE"

echo ""
echo "=== Done ==="
