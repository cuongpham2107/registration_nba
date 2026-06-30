<?php

namespace App\Filament\Resources\GuestCardResource\Pages;

use App\Filament\Resources\GuestCardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuestCards extends ListRecords
{
    protected static string $resource = GuestCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modal()
                ->modalHeading('Thêm thẻ khách mới')
                ->modalDescription('Nhập thông tin thẻ khách mới'),
        ];
    }
}
