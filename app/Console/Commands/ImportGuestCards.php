<?php

namespace App\Console\Commands;

use App\Models\GuestCard;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportGuestCards extends Command
{
    protected $signature = 'app:import-guest-cards {file : Path to CSV file}';

    protected $description = 'Import guest cards data from CSV (Book 1(Sheet1)-4.csv)';

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

            if (count($row) < 5) {
                $skipped++;
                continue;
            }

            $stt = $row[0] ?? '';
            $fullName = $row[1] ?? '';
            $unit = $row[2] ?? '';
            $unitAbbr = $row[3] ?? '';
            $title = $row[4] ?? '';
            $cardNumber = $row[5] ?? '';
            $issuedAtStr = $row[6] ?? '';
            $issueArea = $row[7] ?? '';

            // Detect section header
            if ($stt !== '' && $fullName === '' && $unit !== '' && !is_numeric($stt)) {
                $currentSection = $unit;
                $skipped++;
                continue;
            }

            // Detect section from card number
            if ($cardNumber && preg_match('#^ASG/([A-Z]+)#', $cardNumber, $m)) {
                $currentSection = $m[1];
            }

            if ($fullName === '' && $unit === '' && $cardNumber === '') {
                $skipped++;
                continue;
            }

            $issuedAt = $this->parseDate($issuedAtStr);

            GuestCard::create([
                'stt' => is_numeric($stt) ? (int)$stt : null,
                'full_name' => $fullName ?: null,
                'unit' => $unit ?: null,
                'unit_abbr' => $unitAbbr ?: null,
                'title' => $title ?: null,
                'card_number' => $cardNumber ?: null,
                'issued_at' => $issuedAt,
                'issue_area' => $issueArea ?: null,
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

        try {
            return Carbon::createFromFormat('d/m/Y', $dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            try {
                return Carbon::createFromFormat('d/m/y', $dateStr)->format('Y-m-d');
            } catch (\Exception $e) {
                $this->warn("Cannot parse date: {$dateStr}");
                return null;
            }
        }
    }
}
