<?php

namespace App\Console\Commands;

use App\Models\VehicleCard;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportVehicleCards extends Command
{
    protected $signature = 'app:import-vehicle-cards {file : Path to CSV file}';

    protected $description = 'Import vehicle cards data from CSV, converting dd/mm/YYYY dates to datetime';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->error("Cannot open file: {$filePath}");
            return 1;
        }

        // Skip the header row
        $header = fgetcsv($handle, 0, ',', '"', '');
        if (!$header) {
            $this->error('Empty CSV file');
            fclose($handle);
            return 1;
        }

        $bar = $this->output->createProgressBar();
        $bar->start();

        $inserted = 0;
        $skipped = 0;
        $currentSection = null;

        while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $row = array_map(fn($v) => is_string($v) ? trim($v) : $v, $row);

            if (count($row) < 6) {
                $skipped++;
                continue;
            }

            // Normalize fields
            $stt = $row[0] ?? '';
            $fullName = $row[1] ?? '';
            $unit = $row[2] ?? '';
            $unitAbbr = $row[3] ?? '';
            $title = $row[4] ?? '';
            $cardNumber = $row[5] ?? '';
            $issuedAtStr = $row[6] ?? '';
            $issueArea = $row[7] ?? '';
            $phone = $row[8] ?? '';
            $licensePlate = $row[9] ?? '';
            $vehicleType = $row[10] ?? '';

            // Detect section header (STT like "D" or non-numeric)
            if ($stt !== '' && $fullName === '' && $unit !== '' && !is_numeric($stt)) {
                $currentSection = $unit;
                $this->line("\nSection: {$unit}");
                $skipped++;
                continue;
            }

            // Track section from first card_number prefix
            if ($cardNumber) {
                if (str_starts_with($cardNumber, 'ASG/VIAGS')) {
                    $currentSection = 'VIAGS';
                } elseif (str_starts_with($cardNumber, 'ASG/ALPHA')) {
                    $currentSection = 'ALPHA';
                } elseif (str_starts_with($cardNumber, 'ASG/KGL')) {
                    $currentSection = 'KGL';
                } elseif (str_starts_with($cardNumber, 'HAN.ASG/')) {
                    $currentSection = 'Vehicle';
                } elseif ($cardNumber === '' && $unit === '') {
                    $currentSection = 'Unknown';
                }
            }

            // If really empty data row, skip
            if ($fullName === '' && $unit === '' && $cardNumber === '' && $licensePlate === '' && $vehicleType === '') {
                $skipped++;
                continue;
            }

            // Parse date
            $issuedAt = $this->parseDate($issuedAtStr);

            VehicleCard::create([
                'stt' => is_numeric($stt) ? (int)$stt : null,
                'full_name' => $fullName ?: null,
                'unit' => $unit ?: null,
                'unit_abbr' => $unitAbbr ?: null,
                'title' => $title ?: null,
                'card_number' => $cardNumber ?: null,
                'issued_at' => $issuedAt,
                'issue_area' => $issueArea ?: null,
                'phone' => $phone ?: null,
                'license_plate' => $licensePlate ?: null,
                'vehicle_type' => $vehicleType ?: null,
                'source_section' => $currentSection,
            ]);

            $inserted++;
            $bar->advance();
        }

        fclose($handle);

        $bar->finish();
        $this->newLine(2);
        $this->info("Inserted: {$inserted}, Skipped: {$skipped}");

        return 0;
    }

    private function parseDate(?string $dateStr): ?string
    {
        if ($dateStr === null || trim($dateStr) === '') {
            return null;
        }

        $dateStr = trim($dateStr);

        // Try dd/mm/YYYY
        try {
            return Carbon::createFromFormat('d/m/Y', $dateStr)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // Try dd/mm/yy
            try {
                return Carbon::createFromFormat('d/m/y', $dateStr)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                $this->warn("Cannot parse date: {$dateStr}");
                return null;
            }
        }
    }
}
