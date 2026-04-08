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
        // Chuyển đổi string areas thành array (nếu có dấu phẩy)
        $areas = isset($row['areas']) ? explode(',', $row['areas']) : [];
        $areas = array_map('trim', $areas); // Loại bỏ khoảng trắng thừa

        return new Guest([
            'name' => $row['name'] ?? null,
            'papers' => $row['papers'] ?? null,
            'type' => $row['type'] ?? null,
            'areas' => $areas, // Sử dụng 'areas' và là array
            'license_plate' => $row['license_plate'] ?? null,
            'note' => $row['note'] ?? null,
            'visitor_registration_id' => $row['visitor_registration_id'] ?? null,
        ]);
    }
}
