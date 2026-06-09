<?php

namespace App\Filament\Resources\RegistrationEntries\Pages;

use App\Filament\Resources\RegistrationEntries\RegistrationEntryResource;
use App\Models\Fee;
use App\Models\Guest;
use App\Models\RegistrationEntry;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class ListRegistrationEntries extends ListRecords
{
    protected static string $resource = RegistrationEntryResource::class;

    protected string $view = 'filament.resources.registration-entry-resource.pages.list-registration-entries';

    #[On('refresh-table')]
    public function refreshTable(): void
    {
        $this->resetTable();
    }

    protected function applyFiltersToTableQuery(Builder $query, bool $isResolvingRecord = false): Builder
    {
        $query = parent::applyFiltersToTableQuery($query);
        $query->getQuery()->orders = null;
        $query->orderByRaw("
            CASE
                WHEN status = 'coming_in' THEN 0
                WHEN status = 'came_out' THEN 1
                WHEN status = 'none' OR status IS NULL OR status = '' THEN 2
                ELSE 3
            END ASC,
            created_at DESC
        ");

        return $query;
    }

    public function getHeading(): string
    {
        return 'Danh sách đơn đăng ký';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_registration_entry')
                ->label('Tạo đăng ký kiểm hoá')
                ->modalWidth(Width::Large)
                ->icon('heroicon-s-plus')
                ->hidden(function (): bool {
                    $user = auth()->user();

                    return ! $user || ! $user->can('create', RegistrationEntry::class);
                })
                ->schema([
                    TextInput::make('license_plate')
                        ->label('Biển số xe')
                        ->required(),
                    Select::make('fee_id')
                        ->label('Loại phương tiện')
                        ->options(Fee::all()->pluck('vehicle_type', 'id')->toArray()
                        )
                        ->required()
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data): void {
                    try {
                        $guest = Guest::create([
                            'name' => $data['license_plate'], // Tạm thời lấy tên khách bằng biển số xe, có thể chỉnh lại sau nếu cần
                            'phone' => null,
                            'papers' => null,
                            'type' => null,
                            'areas' => null,
                            'license_plate' => $data['license_plate'],
                            'fee_id' => $data['fee_id'],
                            'note' => null,
                        ]);
                        // dd($guest);

                        RegistrationEntry::create([
                            'name' => $data['license_plate'],
                            'papers' => null,
                            'license_plate' => $data['license_plate'] ?? null,
                            'guest_id' => $guest->id,
                            'start_date' => now(),
                            'type' => 'inspection',
                            'job' => null,
                            'status' => 'none',
                        ]);
                        Notification::make()
                            ->success()
                            ->title('Tạo đăng ký thành công')
                            ->body('Đã tạo đơn đăng ký kiểm hoá cho khách có biển số '.$data['license_plate'].'.')
                            ->send();
                    } catch (\Throwable $e) {
                        report($e);

                        Notification::make()
                            ->danger()
                            ->title('Có lỗi xảy ra')
                            ->body('Không thể tạo đơn đăng ký. Vui lòng thử lại hoặc liên hệ quản trị viên.')
                            ->send();
                    }

                    try {
                        $protectUsers = User::whereHas('roles', fn ($query) => $query->where('name', 'protect'))->get();
                        foreach ($protectUsers as $user) {
                            if (! $user instanceof Model) {
                                continue;
                            }
                            Notification::make()
                                ->title('Đơn xét duyệt đăng ký xe kiểm hoá')
                                ->success()
                                ->broadcast($user);
                        }
                    } catch (\Exception $e) {
                        Log::error('Broadcast notification failed: '.$e->getMessage());
                    }
                })
                ->modalSubmitAction(fn (Action $action) => $action
                    ->label('Tạo')
                ),

        ];
    }

    #[On('card-scanned')]
    public function onCardScanned(string $code): void
    {
        $record = RegistrationEntry::query()
            ->whereHas('card', fn ($query) => $query->where('account_id', $code))
            ->where('status', 'entering')
            ->first();
        if (! $record) {
            Notification::make()
                ->title('Không tìm thấy bản ghi')
                ->body("Không tìm thấy đơn đăng ký đang ở trạng thái 'Đang vào' với mã thẻ: {$code}.")
                ->warning()
                ->send();

            return;
        }

        $this->mountTableAction('return-card', $record->getKey());
    }
}
