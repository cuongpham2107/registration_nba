<?php

namespace App\Filament\Resources\Cards\Pages;

use App\Filament\Resources\Cards\CardResource;
use App\Models\Card;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListCards extends ListRecords
{
    protected static string $resource = CardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Thêm thẻ mới')
                ->icon('heroicon-o-plus')
                ->modalHeading('Thêm thẻ mới'),
        ];
    }

    #[On('card-scanned')]
    public function onCardScanned(string $code): void
    {
        $card = Card::query()
            ->where('card_number', $code)
            ->orWhere('account_id', $code)
            ->first();

        if (! $card) {
            Notification::make()
                ->title('Không tìm thấy thẻ')
                ->body("Không tìm thấy thẻ nào với mã: {$code}.")
                ->warning()
                ->send();

            return;
        }

        $this->mountTableAction('edit', $card);
    }
}
