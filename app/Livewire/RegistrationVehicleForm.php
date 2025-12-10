<?php

namespace App\Livewire;

use App\Models\RegistrationVehicle;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class RegistrationVehicleForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public bool $hawbChecking = false;
    public ?string $hawbMessage = null;
    public ?string $hawbMessageType = null; // 'success' or 'error'

    public function mount(): void
    {
        $this->form->fill([
            'expected_in_at' => now()->format('Y-m-d H:i'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Thông tin tài xế')
                    ->description('Vui lòng điền đầy đủ thông tin tài xế')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('driver_name')
                            ->label('Tên tài xế')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('name')
                            ->label('Tên đơn vị')
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('driver_id_card')
                            ->label('Số CCCD/CMND')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('driver_phone')
                            ->label('Số điện thoại')
                            ->tel()
                            ->maxLength(20)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Thông tin xe và hàng hóa')
                    ->description('Thông tin về xe và lô hàng')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        TextInput::make('vehicle_number')
                            ->label('Biển số xe')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('hawb_number')
                            ->label('Số HAWB')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(function ($state) {
                                $this->checkHawbNumber($state);
                            })
                            ->helperText(fn () => $this->getHawbHelperText())
                            ->columnSpan(1),

                        TextInput::make('pcs')
                            ->label('PCS')
                            ->maxLength(255)
                            ->columnSpan(1),

                        DateTimePicker::make('expected_in_at')
                            ->label('Thời gian vào dự kiến')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Ghi chú')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Ghi chú')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn () => empty($this->data['notes'])),
            ])
            ->statePath('data');
    }

    protected function getHawbHelperText(): ?string
    {
        if ($this->hawbChecking) {
            return '🔄 Đang kiểm tra số HAWB...';
        }

        if ($this->hawbMessage) {
            return $this->hawbMessage;
        }

        return null;
    }

    public function checkHawbNumber(?string $hawbNumber): void
    {
        if (empty($hawbNumber)) {
            $this->hawbMessage = null;
            $this->hawbMessageType = null;
            return;
        }

        $this->hawbChecking = true;
        $this->hawbMessage = null;

        try {
            $response = Http::timeout(10)->get("https://wh-nba.asgl.net.vn/api/hawb-info/{$hawbNumber}");
            $data = $response->json();

            if ($data['success'] ?? false && isset($data['data']['plan'])) {
                $plan = $data['data']['plan'];
                $this->hawbMessage = "✓ Số HAWB hợp lệ - Dest: " . ($plan['Dest'] ?? 'N/A') . 
                                     ", PCS: " . ($plan['Pcs'] ?? 'N/A') . 
                                     ", Agent: " . ($plan['Agent'] ?? 'N/A');
                $this->hawbMessageType = 'success';

                // Tự động điền PCS nếu có và chưa được điền
                if (isset($plan['Pcs']) && empty($this->data['pcs'])) {
                    $this->data['pcs'] = (string) $plan['Pcs'];
                }
            } else {
                $this->hawbMessage = '⚠️ Số HAWB không tồn tại trong hệ thống';
                $this->hawbMessageType = 'error';
            }
        } catch (\Exception $e) {
            $this->hawbMessage = '❌ Lỗi kết nối đến server. Vui lòng thử lại.';
            $this->hawbMessageType = 'error';
            Log::error('HAWB check error: ' . $e->getMessage());
        } finally {
            $this->hawbChecking = false;
        }
    }

    public function create(): void
    {
        $data = $this->form->getState();

        // Kiểm tra trùng lặp với điều kiện thời gian 4 tiếng
        $newExpectedTime = Carbon::parse($data['expected_in_at']);

        $existingRegistration = RegistrationVehicle::where('name', $data['name'])
            ->where('driver_name', $data['driver_name'])
            ->where('driver_phone', $data['driver_phone'])
            ->where('driver_id_card', $data['driver_id_card'])
            ->where('vehicle_number', $data['vehicle_number'])
            ->where('hawb_number', $data['hawb_number'])
            ->orderBy('expected_in_at', 'desc')
            ->first();

        if ($existingRegistration) {
            $existingTime = Carbon::parse($existingRegistration->expected_in_at);
            $hoursDifference = $newExpectedTime->diffInHours($existingTime, false);

            if (abs($hoursDifference) < 4) {
                Notification::make()
                    ->title('Lỗi đăng ký')
                    ->body('Đăng ký trước đó thành công rồi phải vào giờ khác (ít nhất cách 4 tiếng).')
                    ->danger()
                    ->duration(5000)
                    ->send();
                return;
            }
        }

        $data['status'] = 'none';
        $record = RegistrationVehicle::create($data);

        // Gửi email và thông báo
        $this->sendEmailAndNotifications($record);

        Notification::make()
            ->title('Đăng ký thành công!')
            ->body('Đăng ký xe đã được tạo và gửi email thành công!')
            ->success()
            ->duration(5000)
            ->send();

        // Reset form
        $this->form->fill([
            'driver_name' => null,
            'name' => null,
            'driver_id_card' => null,
            'driver_phone' => null,
            'vehicle_number' => null,
            'hawb_number' => null,
            'pcs' => null,
            'expected_in_at' => now()->format('Y-m-d H:i'),
            'notes' => null,
        ]);

        $this->hawbMessage = null;
        $this->hawbMessageType = null;
    }

    protected function sendEmailAndNotifications(RegistrationVehicle $record): void
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
                    $mail = (new \App\Services\MailService())->sendMailWithTemplate(
                        $user->email,
                        'Đăng ký xe khai thác: ' . $record->driver_name . ' | ' . $record->vehicle_number . ' | ' . date('Y-m-d H:i:s'),
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
                        ->title('Đăng ký xe khai thác mới')
                        ->success()
                        ->body("Đăng ký xe {$record->vehicle_number} - Tài xế: {$record->driver_name} cần phê duyệt.")
                        ->sendToDatabase($user);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notifications: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.registration-vehicle-form')
            ->layout('components.layouts.public');
    }
}
