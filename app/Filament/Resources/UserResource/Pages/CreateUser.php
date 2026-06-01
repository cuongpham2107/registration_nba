<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'users_email_unique')) {
                Notification::make()
                    ->title('Đăng ký không thành công')
                    ->body('Email này đã được sử dụng bởi tài khoản khác. Vui lòng nhập email khác.')
                    ->danger()
                    ->send();
                
                // Prevent redirect by returning null
                return null;
            }
            
            // Re-throw other exceptions
            throw $e;
        }
    }
}
