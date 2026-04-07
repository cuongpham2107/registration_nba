<?php

namespace App\Filament\Resources\LiftingServiceFees\Pages;

use App\Filament\Resources\LiftingServiceFees\LiftingServiceFeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLiftingServiceFees extends ListRecords
{
    protected static string $resource = LiftingServiceFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver(),
        ];
    }
}
