<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use Closure;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use App\Filament\Resources\RegistrationResource;
use App\Services\RegistrationService;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected static string $view = "filament.resources.registrations.pages.list-registrations";

    // Listen for the refresh-registration-table event
    #[On('refresh-registration-table')]
    public function refreshTable(): void
    {
        // This will refresh the entire Livewire component and reload the table
        $this->resetTable();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Đăng ký khách mới')
                ->icon('heroicon-o-plus')
                ->modalWidth(MaxWidth::SixExtraLarge)
                ->modalHeading('Đăng ký khách mới')
                ->extraModalFooterActions(fn (Actions\CreateAction $action): array => [
                    $action->makeModalSubmitAction('createAndSendMail', arguments: ['send_mail' => true])
                        ->label('Tạo và gửi phê duyệt')
                        ->color('success')
                        ->icon('heroicon-m-envelope'),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $user = Auth::user();
                    if ($user) {
                        $data['user_id'] = $user->id;
                        // Nếu là approver thì gắn approver_id = chính tài khoản đó
                        if ($user->hasRole('approver')) {
                            $data['approver_id'] = $user->id;
                        }
                        // Không phải approver thì chỉ gắn user_id, không set approver_id
                    }
                    return $data;
                })
                ->after(function (Registration $record, Actions\CreateAction $action): void {
                    $user = Auth::user();
                    // Nếu là approver: tạo bản ghi trực tiếp và duyệt luôn
                    if ($user && $user->hasRole('approver')) {
                        try {
                            (new \App\Services\RegistrationService())->createRegistrationDirectly($record);
                            $record->type = 'browse';
                            $record->type_date = now();
                            $record->status = 'sent';
                            $record->save();

                            \Filament\Notifications\Notification::make()
                                ->title('Đăng ký thành công')
                                ->success()
                                ->body('Đăng ký đã được tạo và phê duyệt tự động.')
                                ->send();
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error('Auto approve failed: ' . $e->getMessage());
                            \Filament\Notifications\Notification::make()
                                ->title('Lỗi tạo bản ghi')
                                ->danger()
                                ->body('Đăng ký đã tạo nhưng không thể phê duyệt tự động: ' . $e->getMessage())
                                ->send();
                        }
                    } else {
                        // Nếu người dùng không phải approver và nhấn "Tạo và gửi mail luôn"
                        if ($action->getArguments()['send_mail'] ?? false) {
                            (new \App\Services\RegistrationService())->sendMailForRegistration($record);
                        }
                    }
                }),
        ];
    }
    protected function getTableRecordActionUsing(): ?Closure
        {
            return null;
        }
    public function getHeading(): string
    {
        return 'Danh sách đăng ký khách';
    }
  
}
