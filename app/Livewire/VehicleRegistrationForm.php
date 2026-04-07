<?php

namespace App\Livewire;

use App\Models\GatheringPointFee;
use App\Models\LiftingServiceFee;
use App\Models\User;
use App\Models\VehicleRegistration;
use App\Services\MailService;
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

class VehicleRegistrationForm extends Component implements HasSchemas
{
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
            ->extraAttributes(['style' => 'gap: 1rem;', 'class' => 'bg-white'])
            ->columns(2)
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
                    ->columnSpan(2),

                TextInput::make('driver_id_card')
                    ->label('Số CCCD/CMND')
                    ->required()
                    ->validationMessages([
                        'required' => 'Số CCCD/CMND không được để trống.',
                    ])
                    ->extraAttributes(['class' => '!bg-gray-100'])
                    ->maxLength(255)
                    ->columnSpan(1),

                TextInput::make('driver_phone')
                    ->label('Số điện thoại')
                    ->tel()
                    ->extraAttributes(['class' => '!bg-gray-100'])
                    ->maxLength(20)
                    ->required()
                    ->columnSpan(1),

                TextInput::make('vehicle_number')
                    ->label('Biển số xe')
                    ->required()
                    ->extraAttributes(['class' => '!bg-gray-100'])
                    ->validationMessages([
                        'required' => 'Biển số xe không được để trống.',
                    ])
                    ->maxLength(255)
                    ->columnSpan(2),
                DateTimePicker::make('expected_in_at')
                    ->label('Thời gian vào dự kiến')
                    ->required()
                    ->native(true)
                    ->extraAttributes(['class' => '!bg-gray-100'])
                    ->seconds(false)
                    ->displayFormat('H:i d/m/Y')
                    ->columnSpan(2),
                Select::make('gathering_point_fee_id')
                    ->label('Loại xe, trọng tải (Biểu phí địa điểm tập trung)')
                    ->options(GatheringPointFee::where('is_active', true)->pluck('vehicle_type', 'id'))
                    ->required()
                    ->native(false)
                    ->columnSpan(2),
                Toggle::make('has_lifting_service')
                    ->label('Có sử dụng dịch vụ nâng hạ không?')
                    ->onIcon('heroicon-o-check')
                    ->offIcon('heroicon-o-x-mark')
                    ->onColor('success')
                    ->inline(false)
                    ->columnSpan(2)
                    ->live(),
                Select::make('lifting_service_fee_id')
                    ->label('Loại dịch vụ nâng hạ')
                    ->options(LiftingServiceFee::where('is_active', true)->pluck('service_name', 'id'))
                    ->visible(fn (Get $get): bool => $get('has_lifting_service') === true)
                    ->native(false)
                    ->columnSpan(2),
                Textarea::make('notes')
                    ->label('Ghi chú')
                    ->rows(2)
                    ->extraAttributes(['class' => '!bg-gray-100'])
                    ->maxLength(1000)
                    ->columnSpan(2),
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

        // If has_lifting_service was not checked, remove lifting_service_fee_id
        if (empty($data['has_lifting_service'])) {
            $processedData['lifting_service_fee_id'] = null;
        }

        $processedData['status'] = 'sent';
        $record = VehicleRegistration::create($processedData);

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
