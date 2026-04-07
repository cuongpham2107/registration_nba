<?php

namespace App\Filament\Resources\RegistrationEntries\Pages;

use App\Filament\Resources\RegistrationEntries\RegistrationEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRegistrationEntry extends EditRecord
{
    protected static string $resource = RegistrationEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
