<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\GatheringPointFee;
use App\Models\LiftingServiceFee;
use App\Models\User;
use App\Models\VehicleRegistration;
use App\Services\MailService;
use Closure;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class VehicleRegistrationForm extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?array $data = [];

    public bool $isListRegistered = false;

    public string $searchDriver = '';

    // Hold registrations to display in the view
    public array $registrations = [];

    public function showRegisteredList(): void
    {
        $this->isListRegistered = ! $this->isListRegistered;

        if ($this->isListRegistered) {
            $this->loadRegistrations();
        } else {
            $this->registrations = [];
        }
    }

    public function applySearch(): void
    {
        $this->isListRegistered = true;
        $this->loadRegistrations();
    }

    public function updatedSearchDriver(): void
    {
        $this->loadRegistrations();
    }

    private function loadRegistrations(): void
    {
        $query = VehicleRegistration::whereDate('created_at', now()->toDateString())
            ->whereIn('status', ['sent', 'approve'])
            ->orderByRaw("CASE 
                WHEN status = 'approve' THEN 1 
                WHEN status = 'sent' THEN 2
            END ASC")
            ->orderBy('created_at', 'desc');

        if (! empty($this->searchDriver)) {
            $query->where(function ($q) {
                $q->where('driver_name', 'like', '%'.$this->searchDriver.'%')
                    ->orWhere('vehicle_number', 'like', '%'.$this->searchDriver.'%');
            });
        }

        $rows = $query->limit(50)->get();

        $this->registrations = $rows->map(function (VehicleRegistration $r) {

            // Map status to label and color classes (approximate Filament badge colors)
            $statusLabel = match ($r->status) {
                'none' => 'Chưa gửi',
                'sent' => 'Chờ phê duyệt',
                'approve' => 'Đã phê duyệt',
                'entering' => 'Đang vào',
                'exited' => 'Đã ra',
                'reject' => 'Từ chối',
                default => $r->status,
            };

            $statusColorClass = match ($r->status) {
                'none' => 'bg-gray-100 text-gray-700',
                'sent' => 'bg-yellow-100 text-yellow-800',
                'approve' => 'bg-green-100 text-green-800',
                'entering' => 'bg-indigo-100 text-indigo-800',
                'exited' => 'bg-blue-100 text-blue-800',
                'reject' => 'bg-red-100 text-red-800',
                default => 'bg-gray-100 text-gray-700',
            };

            return [
                'id' => $r->id,
                'driver_name' => $r->driver_name,
                'vehicle_number' => $r->vehicle_number,
                'expected_in_at' => $r->expected_in_at ? $r->expected_in_at->format('d/m/Y H:i') : null,
                'status' => $r->status,
                'status_label' => $statusLabel,
                'status_classes' => $statusColorClass,
            ];
        })->toArray();
    }

    public function mount(): void
    {
        $this->form->fill([
            'expected_in_at' => now()->format('Y-m-d H:i'),
        ]);

        // Load data from localStorage if available via JavaScript
        $this->dispatch('load-stored-data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->record(new VehicleRegistration)
            ->extraAttributes(['style' => 'gap: 1rem;', 'class' => 'bg-white'])
            ->columns(6)
            ->components([
                TextInput::make('driver_name')
                    ->label('Tên tài xế')
                    ->required()
                    ->validationMessages([
                        'required' => 'Tên tài xế không được để trống.',
                    ])
                    ->extraAttributes([
                        'class' => '!bg-gray-100',
                    ])
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('driver_id_card')
                    ->label('Số CCCD/CMND')
                    ->required()
                    ->validationMessages([
                        'required' => 'Số CCCD/CMND không được để trống.',
                    ])
                    ->extraAttributes(['class' => '!bg-gray-100'])
                    ->maxLength(255)
                    ->columnSpan(3),

                TextInput::make('driver_phone')
                    ->label('Số điện thoại')
                    ->tel()
                    ->extraAttributes(['class' => '!bg-gray-100'])
                    ->maxLength(20)
                    ->required()
                    ->validationMessages([
                        'required' => 'Số điện thoại không được để trống.',
                    ])
                    ->columnSpan(3),

                TextInput::make('vehicle_number')
                    ->label('Biển số xe')
                    ->required()
                    ->extraAttributes(['class' => '!bg-gray-100'])
                    ->validationMessages([
                        'required' => 'Biển số xe không được để trống.',
                    ])
                    ->maxLength(255)
                    ->columnSpanFull(),
                DateTimePicker::make('expected_in_at')
                    ->label('Thời gian vào dự kiến')
                    ->required()
                    ->native(true)
                    ->extraAttributes(['class' => '!bg-gray-100'])
                    ->seconds(false)
                    ->displayFormat('H:i d/m/Y')
                    ->validationMessages([
                        'required' => 'Thời gian vào dự kiến không được để trống.',
                    ])
                    ->columnSpanFull(),
                Select::make('gathering_point_fee_id')
                    ->label('Loại xe, trọng tải (Biểu phí địa điểm tập trung)')
                    ->options(GatheringPointFee::where('is_active', true)->pluck('vehicle_type', 'id'))
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'Vui lòng chọn loại xe / trọng tải.',
                    ])
                    ->columnSpanFull(),
                Toggle::make('has_lifting_service')
                    ->label('Có sử dụng dịch vụ nâng hạ không?')
                    ->onIcon('heroicon-o-check')
                    ->offIcon('heroicon-o-x-mark')
                    ->onColor('success')
                    ->inline(true)
                    ->columnSpanFull()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set): void {
                        if ($state !== true) {
                            $set('lifting_service_fee_id', null);
                            $set('count_package', null);
                        }
                    }),
                Select::make('lifting_service_fee_id')
                    ->label('Loại dịch vụ nâng hạ')
                    ->options(LiftingServiceFee::where('is_active', true)->pluck('service_name', 'id'))
                    ->visible(fn (Get $get): bool => $get('has_lifting_service') === true)
                    ->native(false)
                    ->required(fn (Get $get): bool => $get('has_lifting_service') === true)
                    ->live()
                    ->reactive()
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'Vui lòng chọn loại dịch vụ nâng hạ.',
                    ])
                    ->afterStateUpdated(function ($state, callable $set): void {
                        // Reset dependent field when changing fee type.
                        $set('count_package', null);
                    })
                    ->columnSpan(function (Get $get): int {
                        $liftingServiceFee = LiftingServiceFee::query()
                            ->select(['id', 'weight_category'])
                            ->find($get('lifting_service_fee_id'));

                        if ($liftingServiceFee?->weight_category === 'under_2_tons') {
                            return 4;
                        }

                        return 6;
                    }),
                TextInput::make('count_package')
                    ->label('Số kiện')
                    ->required()
                    ->validationMessages([
                        'required' => 'Số kiện không được để trống.',
                    ])
                    ->extraAttributes(['class' => '!bg-gray-100'])
                    ->maxLength(255)
                    ->visible(function (Get $get): bool {
                        $liftingServiceFee = LiftingServiceFee::query()
                            ->select(['id', 'weight_category'])
                            ->find($get('lifting_service_fee_id'));

                        return $liftingServiceFee?->weight_category === 'under_2_tons';
                    })
                    ->columnSpan(2),

                Toggle::make('wants_invoice')
                    ->label('Có xuất hoá đơn hay không?')
                    ->onIcon('heroicon-o-check')
                    ->offIcon('heroicon-o-x-mark')
                    ->onColor('success')
                    ->inline(true)
                    ->columnSpanFull()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set): void {
                        if ($state !== true) {
                            $set('company_id', null);
                        }
                    }),

                Select::make('company_id')
                    ->label('Chọn công ty')
                    ->relationship(name: 'company', titleAttribute: 'name')
                    ->searchable()
                    ->native(false)
                    ->preload()
                    ->visible(fn (Get $get): bool => $get('wants_invoice') === true)
                    ->required(fn (Get $get): bool => $get('wants_invoice') === true)
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            // When user wants an invoice, company must be selected/created.
                            if ($get('wants_invoice') === true && blank($value)) {
                                $fail('Vui lòng chọn hoặc tạo công ty để xuất hoá đơn.');
                            }

                            // Defensive: when user doesn't want invoice, company should not be set.
                            if ($get('wants_invoice') !== true && filled($value)) {
                                $fail('Không thể chọn công ty khi không xuất hoá đơn.');
                            }
                        },
                    ])
                    ->validationMessages([
                        'required' => 'Vui lòng chọn hoặc tạo công ty để xuất hoá đơn.',
                    ])
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Tên công ty')
                            ->required(),
                        TextInput::make('tax_code')
                            ->label('Mã số thuế'),
                        TextInput::make('email')
                            ->label('Email')
                            ->email(),
                        TextInput::make('phone')
                            ->label('Số điện thoại'),
                        TextInput::make('address')
                            ->label('Địa chỉ'),
                    ])
                    ->editOptionForm([
                        TextInput::make('name')
                            ->label('Tên công ty')
                            ->required(),
                        TextInput::make('tax_code')
                            ->label('Mã số thuế'),
                        TextInput::make('email')
                            ->label('Email')
                            ->email(),
                        TextInput::make('phone')
                            ->label('Số điện thoại'),
                        TextInput::make('address')
                            ->label('Địa chỉ'),
                    ])
                    ->helperText(function (Get $get): ?string {
                        $company = Company::query()
                            ->select(['id', 'name', 'tax_code', 'email', 'phone', 'address'])
                            ->find($get('company_id'));

                        if (! $company) {
                            return 'Chọn hoặc tạo công ty để xuất hoá đơn.';
                        }
                        $lines = [];
                        $lines[] = 'Tên: '.$company->name;

                        if (filled($company->tax_code)) {
                            $lines[] = ' | Mã số thuế: '.$company->tax_code;
                        }

                        return implode("\n", $lines);
                    })
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label('Ghi chú')
                    ->rows(2)
                    ->extraAttributes(['class' => '!bg-gray-100'])
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function loadStoredDataFromJs($storedData): void
    {
        if (! empty($storedData)) {
            // Fill form with stored data (excluding HAWB data)
            $formData = [
                'driver_name' => $storedData['driver_name'] ?? '',
                'driver_phone' => $storedData['driver_phone'] ?? '',
                'driver_id_card' => $storedData['driver_id_card'] ?? '',
                'vehicle_number' => $storedData['vehicle_number'] ?? '',
                // 'name' => $storedData['name'] ?? '',
                'name' => null,
                'notes' => $storedData['notes'] ?? '',
                'expected_in_at' => now()->format('Y-m-d H:i'),
                // Keep default hawbs row - always start fresh with 1 empty row

            ];

            $this->form->fill($formData);
        }
    }

    public function create(): void
    {
        $data = $this->form->getState();

        // Process data before saving
        $processedData = $data;

        // Convert name array to comma-separated string
        if (isset($processedData['name']) && is_array($processedData['name'])) {
            $processedData['name'] = implode(', ', $processedData['name']);
        }

        // Remove has_lifting_service toggle - it's only for UI, not saved to DB
        unset($processedData['has_lifting_service']);

        // Invoice fields are UI-only; handled separately
        $wantsInvoice = (bool) ($data['wants_invoice'] ?? false);
        $companyId = $data['company_id'] ?? null;

        unset(
            $processedData['wants_invoice'],
            $processedData['company_id'],
        );

        // If has_lifting_service was not checked, remove lifting_service_fee_id
        if (empty($data['has_lifting_service'])) {
            $processedData['lifting_service_fee_id'] = null;
        }

        // Persist company selection only when invoice is requested.
        // Note: company_id lives on vehicle_registrations in the master-company (belongsTo) design.
        if ($wantsInvoice) {
            $processedData['company_id'] = $companyId;
        } else {
            $processedData['company_id'] = null;
        }

        $processedData['status'] = 'sent';
        $record = VehicleRegistration::create($processedData);

        // In the master-company design (belongsTo), company_id is saved on vehicle_registrations.
        // No extra snapshot creation is needed.

        // Dispatch event for localStorage saving before redirect
        $this->dispatch('registration-success', $data);

        // Redirect to success page with registration data
        $this->redirect(route('registration-vehicle.success'), navigate: true);

        // Store registration data in session for success page
        session()->flash('registration_data', [
            'driver_name' => $record->driver_name,
            'name' => $record->name,
            'vehicle_number' => $record->vehicle_number,
            'expected_in_at' => $record->expected_in_at,
        ]);
    }

    protected function sendEmailAndNotifications(VehicleRegistration $record): void
    {
        try {
            $approvers = User::whereHas('roles', function ($query) {
                $query->where('name', 'approve_vehicle');
            })->orWhereHas('permissions', function ($query) {
                $query->where('name', 'approve_vehicle');
            })->get();

            if ($approvers->isEmpty()) {
                Log::warning('No approvers found for vehicle registration');

                return;
            }

            $mailSent = false;
            foreach ($approvers as $user) {
                if ($user->email) {
                    $mail = (new MailService)->sendMailWithTemplate(
                        $user->email,
                        'Đăng ký xe kiểm hoá: '.$record->driver_name.' | '.$record->vehicle_number.' | '.date('Y-m-d H:i:s'),
                        'template-mail.registration-vehicle',
                        ['registration' => $record]
                    );

                    if ($mail) {
                        $mailSent = true;
                    }
                }
            }

            if ($mailSent) {
                $record->update(['status' => 'sent']);

                // Gửi thông báo real-time
                $approveVehicleUsers = User::role('approve_vehicle')->get();
                foreach ($approveVehicleUsers as $user) {
                    Notification::make()
                        ->title('Đăng ký xe kiểm hoá mới')
                        ->success()
                        ->body("Đăng ký xe {$record->vehicle_number} - Tài xế: {$record->driver_name} cần phê duyệt.")
                        ->sendToDatabase($user);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notifications: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.registration-vehicle-form')->layout('layouts::app');
    }
}
