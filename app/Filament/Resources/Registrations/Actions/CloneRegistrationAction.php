<?php

namespace App\Filament\Resources\Registrations\Actions;

use App\Filament\Resources\Registrations\Schemas\CloneRegistrationForm;
use App\Models\Guest;
use App\Models\Registration;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CloneRegistrationAction
{
    public static function make(): Action
    {
        return Action::make('clone')
            ->label('Sao chép')
            ->icon('heroicon-m-document-duplicate')
            ->size(Size::Small)
            ->color('gray')
            ->modalWidth(Width::SixExtraLarge)
            ->schema(fn (Schema $schema, Registration $record) => CloneRegistrationForm::configure($schema, $record->type ?? 'working'))
            ->fillForm(function (Registration $record): array {
                $data = $record->only([
                    'name', 'purpose', 'type', 'asset', 'note', 'company_id',
                ]);

                $data['user_id'] = Auth::id() ?? $record->user_id;
                $data['start_date'] = $record->start_date;
                $data['end_date'] = $record->end_date;

                $data['guests'] = $record->guests->map(fn (Guest $guest) => [
                    'name' => $guest->name,
                    'papers' => $guest->papers,
                    'type' => $guest->type,
                    'license_plate' => $guest->license_plate,
                    'areas' => $guest->areas ?? [],
                    'note' => $guest->note,
                ])->values()->toArray();

                return $data;
            })
            ->action(function (Registration $record, array $data) {
                $newRecord = Registration::create([
                    'name' => $data['name'],
                    'purpose' => $data['purpose'],
                    'type' => $data['type'] ?? $record->type,
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'status' => 'none',
                    'user_id' => Auth::id() ?? $record->user_id,
                    'company_id' => $data['company_id'] ?? $record->company_id,
                    'asset' => $data['asset'] ?? null,
                    'note' => $data['note'] ?? null,
                ]);

                $guestCount = 0;
                if (! empty($data['guests'])) {
                    foreach ($data['guests'] as $guestData) {
                        Guest::create([
                            'registration_id' => $newRecord->id,
                            'name' => $guestData['name'] ?? '',
                            'papers' => $guestData['papers'] ?? '',
                            'type' => $guestData['type'] ?? '',
                            'license_plate' => $guestData['license_plate'] ?? null,
                            'areas' => $guestData['areas'] ?? [],
                            'note' => $guestData['note'] ?? null,
                        ]);
                        $guestCount++;
                    }
                }

                Notification::make()
                    ->title('Sao chép thành công')
                    ->success()
                    ->body("Đã tạo bản sao #{$newRecord->id} với {$guestCount} khách.")
                    ->send();

                Log::info("Clone: #{$record->id} -> #{$newRecord->id} by user #".Auth::id());
            });
    }
}
