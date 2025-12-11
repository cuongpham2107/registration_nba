<?php

namespace App\Filament\Resources\RegisterDirectlyResource\Actions;

use App\Models\RegisterDirectly;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\DB;

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
                    DB::transaction(function () use ($record) {
                        // Update card status to inactive
                        $record->card->update(['status' => 'inactive']);
                        
                        // Update registration vehicle status
                        $record->registrationVehicle->status = 'exited';
                        $record->registrationVehicle->save();
                        
                        // Update record - remove card_id and set status to came_out
                        $record->update([
                            'status' => 'came_out',
                            'actual_date_out' => Carbon::now('Asia/Ho_Chi_Minh'),
                            'card_id' => null,
                        ]);
                    });

                    Notification::make()
                        ->title('Thành công')
                        ->body('Trả thẻ thành công')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Lỗi')
                        ->body('Có lỗi xảy ra khi trả thẻ: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
