<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Filament\Resources\Registrations\Schemas\RegistrationForm;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Đăng ký khách mới')
                ->icon('heroicon-o-plus')
                ->modalWidth(Width::ScreenTwoExtraLarge)
                ->modalHeading('Đăng ký khách mới')
                ->schema(function (Schema $schema) {
                    // `registrations.type` is an enum: only 'working'.
                    $type = 'working';

                    return RegistrationForm::configure($schema, $type);
                }),
        ];
    }
}
