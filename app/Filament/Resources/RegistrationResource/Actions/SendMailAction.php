<?php

namespace App\Filament\Resources\RegistrationResource\Actions;

use App\Models\Customer;
use App\Models\Registration;
use App\Services\MailService;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class SendMailAction
{
    public static function make(): Action
    {
        return Action::make('sendMail')
            ->label('Gửi')
            ->icon('heroicon-m-envelope')
            ->size(ActionSize::Small)
            ->requiresConfirmation()
            ->hidden(fn(Registration $record) => $record->status === 'sent' || $record->user_id !== auth()->id())
            ->action(function (Registration $record) {
                (new \App\Services\RegistrationService())->sendMailForRegistration($record);
            });
    }
}
