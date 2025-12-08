<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListCards extends ListRecords
{
    protected static string $resource = CardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Thêm thẻ mới')
                ->modalHeading('Tạo thẻ mới')
                ->modalWidth(MaxWidth::Medium)
                // ->slideOver()
                // ->stickyModalHeader()
                // ->stickyModalFooter(),
        ];
    }

    public function getHeading(): string
    {
        return 'Danh sách thẻ';
    }
}
