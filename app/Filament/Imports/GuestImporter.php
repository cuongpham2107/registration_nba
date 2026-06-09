<?php

namespace App\Filament\Imports;

use App\Models\Guest;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GuestImporter implements ToModel, WithHeadingRow
{
    /**
     * @return Guest|null
     */
    public function model(array $row)
    {
        // Chuyển đổi string areas thành array.
        // Hỗ trợ nhiều kiểu phân tách thường gặp trong Excel: ",", ";", xuống dòng.
        $areasRaw = $row['areas'] ?? null;
        if (is_array($areasRaw)) {
            $areas = $areasRaw;
        } else {
            $areasRaw = (string) ($areasRaw ?? '');
            $areas = preg_split('/[;,\n\r]+/', $areasRaw) ?: [];
        }

        $areas = array_values(array_filter(array_map('trim', $areas), static fn ($v) => $v !== ''));

        return new Guest([
            'name' => $row['name'] ?? null,
            'papers' => $row['papers'] ?? null,
            'type' => $row['type'] ?? null,
            'license_plate' => $row['license_plate'] ?? null, // Biển số xe
            'areas' => $areas,                                 // Khu vực (ngay sau biển số)
            'note' => $row['note'] ?? null,
            'visitor_registration_id' => $row['visitor_registration_id'] ?? null,
        ]);
    }
}
