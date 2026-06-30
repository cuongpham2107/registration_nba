#!/bin/bash
set -e

GUEST_FILE="${1:-Book 1(Sheet1)-4.csv}"
VEHICLE_FILE="${2:-Book 1(Sheet1)-5.csv}"

echo "=== Import Cards ==="
echo "Guest cards file: $GUEST_FILE"
echo "Vehicle cards file: $VEHICLE_FILE"

echo ""
echo ">>> Running migrations..."
docker exec -i registration_nba_app php artisan migrate --force

echo ""
echo ">>> Importing guest cards from $GUEST_FILE..."
docker exec -i registration_nba_app php artisan app:import-guest-cards "$GUEST_FILE"

echo ""
echo ">>> Importing vehicle cards from $VEHICLE_FILE..."
docker exec -i registration_nba_app php artisan app:import-vehicle-cards "$VEHICLE_FILE"

echo ""
echo "=== Done ==="
