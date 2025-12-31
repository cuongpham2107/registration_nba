<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use Closure;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use App\Filament\Resources\RegistrationResource;
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
                // Only visible if user has an approver
                ->visible(fn () => Auth::user() && Auth::user()->approver)
                ->mutateFormDataUsing(function (array $data): array {
                    $user = Auth::user();
                    if ($user && $user->approver) {
                        $data['user_id'] = $user->id;
                        $data['approver_id'] = $user->approver->id;
                    }
                    return $data;
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
    // public function getTabs(): array
    // {
    //     return [
    //         'all' => Tab::make()
    //             ->label('Tất cả')
    //             ->icon('heroicon-o-bars-4')
    //             ->badge(Registration::query()->count())
    //             ->badgeColor('success'),
    //         'sent' => Tab::make()
    //             ->label('Đã gửi')
    //             ->icon('heroicon-o-bolt-slash')
    //             ->badgeColor('success')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'sent')),
    //         'not_yet_sent' => Tab::make()
    //             ->label('Chưa gửi')
    //             ->icon('heroicon-o-bolt')
    //             ->badgeColor('danger')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'not_yet_sent')),
    //         'browse' => Tab::make()
    //             ->label('Duyệt')
    //             ->icon('heroicon-o-check')
    //             ->badgeColor('success')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'browse')),
    //         'refuse' => Tab::make()
    //             ->label('Từ chối')
    //             ->icon('heroicon-o-x-mark')
    //             ->badgeColor('danger')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'refuse')),
    //     ];
    // }
    // public function getDefaultActiveTab(): string | int | null
    // {
    //     return 'all';
    // }
  
}
