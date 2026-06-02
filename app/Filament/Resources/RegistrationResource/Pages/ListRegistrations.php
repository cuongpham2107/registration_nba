<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use App\Models\Area;
use App\Models\Fee;
use App\Models\Registration;
use App\Services\RegistrationService;
use Carbon\Carbon;
use Closure;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected static string $view = 'filament.resources.registrations.pages.list-registrations';

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
                        ->icon('heroicon-m-envelope')
                        ->hidden(fn () => ! Auth::user() || Auth::user()->hasRole('approver')), // Ẩn nút này nếu là approver
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
                            (new RegistrationService)->createRegistrationDirectly($record, $record->fee_id ? 'vehicle' : 'passenger');
                            $record->type = 'browse';
                            $record->type_date = now();
                            $record->status = 'sent';
                            $record->save();

                            Notification::make()
                                ->title('Đăng ký thành công')
                                ->success()
                                ->body('Đăng ký đã được tạo và phê duyệt tự động.')
                                ->send();
                        } catch (\Throwable $e) {
                            Log::error('Auto approve failed: '.$e->getMessage());
                            Notification::make()
                                ->title('Lỗi tạo bản ghi')
                                ->danger()
                                ->body('Đăng ký đã tạo nhưng không thể phê duyệt tự động: '.$e->getMessage())
                                ->send();
                        }
                    } else {
                        // Nếu người dùng không phải approver và nhấn "Tạo và gửi mail luôn"
                        if ($action->getArguments()['send_mail'] ?? false) {
                            (new RegistrationService)->sendMailForRegistration($record);
                        }
                    }
                }),
            Actions\Action::make('createWithFee')
                ->label('Tạo đăng ký có phí')
                ->icon('heroicon-o-currency-dollar')
                ->modalWidth(MaxWidth::SixExtraLarge)
                ->modalHeading('Tạo đăng ký có phí')
                ->hidden(fn () => !auth()->user()->can('create_with_fee_registration_registration'))
                ->form([
                    Section::make('Thông tin đăng ký')
                        ->description('Thông tin khách hàng và phương tiện')
                        ->icon('heroicon-o-document-text')
                        ->columns([
                            'sm' => 1,
                            'md' => 2,
                            'lg' => 6,
                        ])
                        ->schema([
                            TextInput::make('name')
                                ->label('Đơn vị khách')
                                ->required()
                                ->columnSpan(3),

                            Select::make('areas')
                                ->label('Khu vực vào')
                                ->multiple()
                                ->options(Area::all()->pluck('name', 'code'))
                                ->searchable()
                                ->preload()
                                ->columnSpan(3),
                            TextInput::make('customer_name')
                                ->label('Tên tài xế')
                                ->required()
                                ->columnSpan(2),
                            TextInput::make('bks')
                                ->prefixIcon('heroicon-o-truck')
                                ->label('BKS ô tô')
                                ->columnSpan(2),
                            TextInput::make('papers')
                                ->label('Số giấy tờ')
                                ->placeholder('Số CCCD/CMND/Passport')
                                ->columnSpan(2),

                            DateTimePicker::make('start_date')
                                ->displayFormat('d/m/Y h:i')
                                ->locale('vi')
                                ->seconds(false)
                                ->label('Giờ vào dự kiến')
                                ->placeholder('Chọn ngày, giờ vào dự kiến')
                                ->prefixIcon('heroicon-o-calendar')
                                ->native(false)
                                ->required()
                                ->columnSpan(3),
                            DateTimePicker::make('end_date')
                                ->displayFormat('d/m/Y h:i')
                                ->locale('vi')
                                ->seconds(false)
                                ->label('Giờ ra dự kiến')
                                ->placeholder('Chọn ngày, giờ kết thúc dự kiến')
                                ->native(false)
                                ->prefixIcon('heroicon-o-calendar')
                                ->required()
                                ->rules([
                                    fn (Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                        if (($record['status'] ?? null) != 'sent') {
                                            if (Carbon::parse($value, 'Asia/Ho_Chi_Minh')->isBefore(Carbon::parse($get('start_date'), 'Asia/Ho_Chi_Minh'))) {
                                                $fail('Ngày, giờ kết thúc phải lớn hơn ngày, giờ bắt đầu.');
                                            }
                                        }

                                    },
                                    fn (Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($record) {
                                        if (($record['status'] ?? null) != 'sent') {
                                            if (Carbon::parse($value, 'Asia/Ho_Chi_Minh')->lessThanOrEqualTo(Carbon::now('Asia/Ho_Chi_Minh'))) {
                                                $fail('Ngày, giờ kết thúc phải lớn hơn ngày, giờ hiện tại.');
                                            }
                                        }

                                    },
                                ])
                                ->columnSpan(3),
                            Select::make('fee_id')
                                ->label('Trọng tải')
                                ->options(Fee::all()->pluck('ticket_code', 'id'))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpanFull(),
                            Textarea::make('purpose')
                                ->label('Mục đích')
                                ->required()
                                ->columnSpanFull(),
                            Textarea::make('note')
                                ->label('Ghi chú')
                                ->rows(2)
                                ->columnSpanFull(),

                        ]),

                ])
                ->extraModalFooterActions(fn (Actions\Action $action): array => [
                    $action->makeModalSubmitAction('createWithFee', arguments: ['send_mail' => true])
                        ->label('Tạo và gửi phê duyệt')
                        ->color('success')
                        ->icon('heroicon-m-envelope')
                        ->hidden(fn () => ! Auth::user() || Auth::user()->hasRole('approver')), // Ẩn nút này nếu là approver
                ])
                ->action(function (array $data, Actions\Action $action): void {
                    try {
                        DB::transaction(function () use ($data, $action) {
                            $user = Auth::user();
                            $registration = Registration::create([
                                'name' => $data['name'],
                                'purpose' => $data['purpose'],
                                'start_date' => $data['start_date'],
                                'end_date' => $data['end_date'],
                                'fee_id' => $data['fee_id'],
                                'note' => $data['note'] ?? null,
                                'status' => 'not_yet_sent',
                                'user_id' => $data['user_id'] ?? Auth::id(),
                                'approver_id' => $user->hasRole('approver') ? $data['approver_id'] ?? null : null,
                            ]);

                            $registration->customers()->create([
                                'name' => $data['customer_name'] ?? '',
                                'papers' => $data['papers'] ?? '',
                                'type' => 'CMND',
                                'areas' => $data['areas'] ?? [],
                                'license_plate' => $data['bks'] ?? '',
                                'note' => $data['note'] ?? null,
                            ]);
                            if ($user && $user->hasRole('approver')) {
                                (new RegistrationService)->createRegistrationDirectly($registration, $registration->fee_id ? 'vehicle' : 'passenger');
                                $registration->update([
                                    'type' => 'browse',
                                    'type_date' => now(),
                                    'status' => 'sent',
                                ]);

                                Notification::make()
                                    ->title('Đăng ký thành công')
                                    ->success()
                                    ->body('Đăng ký đã được tạo và phê duyệt tự động.')
                                    ->send();
                            }

                            if ($action->getArguments()['send_mail'] ?? false) {
                                (new RegistrationService)->sendMailForRegistration($registration);
                            }
                        });

                        Notification::make()
                            ->title('Tạo đăng ký thành công')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Log::error('Create registration with fee failed: '.$e->getMessage());
                        Notification::make()
                            ->title('Lỗi tạo đăng ký')
                            ->danger()
                            ->body('Có lỗi xảy ra: '.$e->getMessage())
                            ->send();
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
