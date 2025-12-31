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
            ->modalIcon('heroicon-o-arrow-uturn-up')
            ->modalHeading(function (RegisterDirectly $record) {
                    if($record->type === 'vehicle') {
                        return 'Xe ra: ' . $record->bks;
                    } else {
                        return 'Người ra: ' . $record->name . ' | CMND: ' . $record->papers;
                    }
                })
            ->modalDescription(function (RegisterDirectly $record) {
                    if($record->type === 'vehicle') {
                        return 'Xe ra khỏi khu vực kiểm soát, thẻ sẽ được trả lại hệ thống.';
                    } else {
                        return 'Người ra khỏi khu vực kiểm soát, thẻ sẽ được trả lại hệ thống.';
                    }
                }
            )
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
                        
                        // Update registration vehicle status (guard and update safely)
                        if ($record->relationLoaded('registrationVehicle') || $record->registrationVehicle) {
                            $registrationVehicle = $record->registrationVehicle;
                            if ($registrationVehicle instanceof \Illuminate\Database\Eloquent\Model) {
                                $registrationVehicle->status = 'exited';
                                $registrationVehicle->save();
                            }
                        }
                        
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
