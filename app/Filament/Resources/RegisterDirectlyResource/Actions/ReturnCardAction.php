<?php

namespace App\Filament\Resources\RegisterDirectlyResource\Actions;

use App\Models\RegisterDirectly;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class ReturnCardAction
{
    public static function make(): Action
    {
        return Action::make('return-card')
            ->label('Ra')
            ->color('danger')
            ->button()
            ->icon('heroicon-o-arrow-uturn-up')
            ->requiresConfirmation()
            ->hidden(
                fn(RegisterDirectly $record) =>
                is_null($record->status) ||
                $record->status === 'none' ||
                $record->status === '' ||
                $record->status === 'came_out'
            )
            ->action(function (RegisterDirectly $record): void {
                try {
                    $record->card->update(['status' => 'inactive']);
                    $record->card_id = null;
                    $record->update([
                        'status' => 'came_out',
                        'actual_date_out' => Carbon::now('Asia/Ho_Chi_Minh'),
                    ]);
                    Notification::make()
                        ->title('Thành công')
                        ->body('Trả thẻ thành công')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Lỗi')
                        ->body('Có lỗi xảy ra khi trả thẻ')
                        ->warning()
                        ->send();
                }
            });
    }
}
