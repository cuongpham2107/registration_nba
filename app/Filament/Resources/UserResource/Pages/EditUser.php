<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'users_email_unique')) {
                Notification::make()
                    ->title('Cập nhật không thành công')
                    ->body('Email này đã được sử dụng bởi tài khoản khác. Vui lòng chọn email khác.')
                    ->danger()
                    ->send();
                
                // Prevent redirect by returning the original record
                return $record;
            }
            
            // Re-throw other exceptions
            throw $e;
        }
    }
}
