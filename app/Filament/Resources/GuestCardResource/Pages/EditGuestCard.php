<?php

namespace App\Filament\Resources\GuestCardResource\Pages;

use App\Filament\Resources\GuestCardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuestCard extends EditRecord
{
    protected static string $resource = GuestCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
