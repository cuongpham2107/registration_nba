<?php

namespace App\Filament\Resources\RegisterDirectlyResource\Pages;

use App\Filament\Resources\RegisterDirectlyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegisterDirectly extends EditRecord
{
    protected static string $resource = RegisterDirectlyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
