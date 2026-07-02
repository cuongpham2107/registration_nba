<?php

namespace App\Filament\Resources\ViolationReportResource\Pages;

use App\Filament\Resources\ViolationReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditViolationReport extends EditRecord
{
    protected static string $resource = ViolationReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
