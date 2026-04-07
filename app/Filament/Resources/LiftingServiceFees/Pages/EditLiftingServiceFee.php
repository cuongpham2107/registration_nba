<?php

namespace App\Filament\Resources\LiftingServiceFees\Pages;

use App\Filament\Resources\LiftingServiceFees\LiftingServiceFeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLiftingServiceFee extends EditRecord
{
    protected static string $resource = LiftingServiceFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
