<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegistration extends EditRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $customer = $this->record->customers()->first();

        if ($customer) {
            $data['customer_name'] = $customer->name;
            $data['paper_type'] = $customer->type;
            $data['bks'] ??= $customer->license_plate;
            $data['papers'] ??= $customer->papers;
            $data['areas'] ??= $customer->areas;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if (filled($this->data['fee_id'] ?? null)) {
            $customer = $this->record->customers()->first();

            if ($customer) {
                $customer->update([
                    'name' => $this->data['customer_name'] ?? '',
                    'papers' => $this->data['papers'] ?? '',
                    'areas' => $this->data['areas'] ?? [],
                    'license_plate' => $this->data['bks'] ?? '',
                ]);
            } else {
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
}
