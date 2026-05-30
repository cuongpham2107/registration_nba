<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateRegistration extends CreateRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function afterCreate(): void
    {
        if (filled($this->data['fee_id'] ?? null)) {
            $this->record->customers()->create([
                'name' => $this->data['customer_name'] ?? '',
                'papers' => $this->data['papers'] ?? '',
                'type' => $this->data['paper_type'] ?? 'CCCD',
                'areas' => $this->data['areas'] ?? [],
                'license_plate' => $this->data['bks'] ?? '',
            ]);
        }
    }
}
