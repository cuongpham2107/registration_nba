<?php

namespace App\Filament\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomerImporter implements ToModel, WithHeadingRow
{
    /**
     * @return Customer|null
     */
    public function model(array $row)
    {
        // Chuyển đổi string areas thành array (nếu có dấu phẩy)
        $areas = isset($row['areas']) ? explode(',', $row['areas']) : [];
        $areas = array_map('trim', $areas); // Loại bỏ khoảng trắng thừa

        return new Customer([
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
