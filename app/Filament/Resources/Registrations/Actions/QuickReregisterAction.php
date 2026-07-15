<?php

namespace App\Filament\Resources\Registrations\Actions;

use App\Models\Guest;
use App\Models\Registration;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QuickReregisterAction
{
    public static function make(): Action
    {
        return Action::make('quickReregister')
            ->label('Đăng ký nhanh lại')
            ->icon('heroicon-m-arrow-path')
            ->size(Size::Small)
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Đăng ký nhanh lại')
            ->modalDescription('Tạo đơn đăng ký mới dựa trên đơn này, với thời gian từ đầu tháng đến cuối tháng.')
            ->modalSubmitActionLabel('Tạo lại')
            ->hidden(function (Registration $record) {
                $user = Auth::user();
                if ($user && $user->hasRole('super_admin')) {
                    return false;
                }

                return $record->status !== 'approve'
                    || ! $record->end_date->isPast()
                    || $record->start_date->diffInDays($record->end_date) < 7;
            })
            ->action(function (Registration $record) {
                $user = Auth::user();

                $newRecord = new Registration;
                $newRecord->name = $record->name;
                $newRecord->purpose = $record->purpose;
                $newRecord->type = $record->type;
                $newRecord->start_date = Carbon::now('Asia/Ho_Chi_Minh')->startOfMonth();
                $newRecord->end_date = Carbon::now('Asia/Ho_Chi_Minh')->endOfMonth()->endOfDay();
                $newRecord->status = 'none';
                $newRecord->user_id = $user?->id ?? $record->user_id;
                $newRecord->company_id = $record->company_id;
                $newRecord->asset = $record->asset;
                $newRecord->note = $record->note;
                $newRecord->save();

                // Copy guests from old registration
                $guests = Guest::where('registration_id', $record->id)->get();
                foreach ($guests as $guest) {
                    $newGuest = $guest->replicate();
                    $newGuest->registration_id = $newRecord->id;
                    $newGuest->save();
                }

                Notification::make()
                    ->title('Đăng ký nhanh lại thành công')
                    ->success()
                    ->body("Đã tạo đơn mới #{$newRecord->id} với {$guests->count()} khách, thời gian từ đầu tháng đến cuối tháng.")
                    ->send();

                Log::info("Quick reregister: #{$record->id} -> #{$newRecord->id} by user #{$user?->id}");
            });
    }
}
