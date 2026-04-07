<?php

namespace App\Filament\Resources\GatheringPointFees\Pages;

use App\Filament\Resources\GatheringPointFees\GatheringPointFeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGatheringPointFee extends EditRecord
{
    protected static string $resource = GatheringPointFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
