<?php

namespace App\Filament\Resources\VisitorRegistrations\Pages;

use App\Filament\Resources\VisitorRegistrations\VisitorRegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVisitorRegistration extends EditRecord
{
    protected static string $resource = VisitorRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
