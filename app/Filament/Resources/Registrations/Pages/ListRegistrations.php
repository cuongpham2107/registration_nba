<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Filament\Resources\Registrations\Schemas\RegistrationForm;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

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
                })
                ->after(function (Model $record) {
                    $user = User::find($record->user_id);
                    $areas = $record->guests
                        ->pluck('areas')
                        ->flatten()
                        ->filter()
                        ->unique()
                        ->implode(', ');

                    // Build notification message
                    $notificationMessage = "Đăng ký khách mới: số {$record->id}\n";
                    $notificationMessage .= "Người y/c: {$user->full_name} ({$user->asgl_id})\n";
                    $notificationMessage .= "Đv khách: {$record->name}\n";
                    $notificationMessage .= "Mục đích: {$record->purpose}\n";
                    $notificationMessage .= "Số lượng khách: {$record->guests->count()} người\n";
                    $notificationMessage .= "Khu vực LV: {$areas}\n";
                    $notificationMessage .= 'Giờ gửi yc: '.now()->format('H:i:s d-m-Y')."\n";

                    // Call webhook API
                    $ch = curl_init();
                    $url = 'http://192.168.1.70:5678/webhook/3abf742a-f6fe-4646-b3c9-4e344bccfe0f';

                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $notificationMessage);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Accept: */*',
                        'User-Agent: Thunder Client (https://www.thunderclient.com)',
                        'x-api-key: 76d43e23a183b85d31f140acca740976',
                        'Content-Type: text/plain',
                    ]);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $error = curl_error($ch);
                    curl_close($ch);

                    // Log the webhook call
                    if ($error) {
                        \Log::error('Webhook call failed', [
                            'error' => $error,
                            'http_code' => $httpCode,
                            'data' => $record,
                        ]);
                    } else {
                        \Log::info('Webhook call successful', [
                            'response' => $response,
                            'http_code' => $httpCode,
                            'data' => $record,
                        ]);
                    }
                }),
        ];

    }
}
