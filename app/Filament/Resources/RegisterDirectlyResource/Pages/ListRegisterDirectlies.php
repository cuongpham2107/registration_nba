<?php

namespace App\Filament\Resources\RegisterDirectlyResource\Pages;

use App\Filament\Resources\RegisterDirectlyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use App\Models\Card;
use Livewire\Attributes\On;

class ListRegisterDirectlies extends ListRecords
{
    protected static string $resource = RegisterDirectlyResource::class;
    
    // Sử dụng custom view để thêm auto-reload script
    protected static string $view = 'filament.resources.register-directly-resource.pages.list-register-directlies';

    // Listen for the refresh-table event
    #[On('refresh-table')]
    public function refreshTable(): void
    {
        // This will refresh the entire Livewire component and reload the table
        $this->resetTable();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Thêm đăng ký mới')
                ->modalHeading('Thêm đăng ký trực tiếp')
                ->modalWidth(MaxWidth::ScreenExtraLarge)
                ->slideOver()
                ->mutateFormDataUsing(function (array $data): array {
                    
                    $card = Card::where('id', $data['card_id'])->first();
                    $card->status = 'active';
                    $card->save();
                    return $data;
                })
        ];
    }

    public function getHeading(): string
    {
        return 'Danh sách đăng ký';
    }
}
