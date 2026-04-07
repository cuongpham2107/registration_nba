<?php

namespace App\Filament\Resources\GatheringPointFees\Pages;

use App\Filament\Resources\GatheringPointFees\GatheringPointFeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGatheringPointFees extends ListRecords
{
    protected static string $resource = GatheringPointFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver(),
        ];
    }
}
