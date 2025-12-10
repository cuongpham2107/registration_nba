<?php

namespace App\Livewire;

use App\Models\RegistrationVehicle;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\Actions\Action;

class RegistrationVehicleForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

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
                Section::make('Thông tin tài xế và xe')
                    ->description('Vui lòng điền đầy đủ thông tin tài xế và xe')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('driver_name')
                            ->label('Tên tài xế')
                            ->required()
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
                        TextInput::make('vehicle_number')
                            ->label('Biển số xe')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Thông tin đơn vị và hàng hóa')
                    ->description('Vui lòng điền đầy đủ thông tin đơn vị và hàng hóa')
                    ->icon('heroicon-o-truck')
                    ->schema([
                       Select::make('name')
                            ->label('Tên đơn vị')
                            ->native(false)
                            ->options($this->getListAgentApi()),
                        TextInput::make('search_hawb')
                            ->label('Tìm kiếm HAWB')
                            ->suffixAction(function () {
                                // Return the Action instance so Filament can render it
                                return Action::make('search-hawb')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->action(function (Get $get) {
                                        // $data contains the whole form state; extract the input value if present
                                        $search = $get('search_hawb') ?? null;
                                        if ($search) {
                                            $this->fetchAndBindHawbs($search);
                                        }
                                    });
                            })
                            ->helperText('Your full name here, including any middle names.'),

                        TableRepeater::make('hawbs')
                            ->label('Danh sách HAWB')
                            ->columns(1)
                            ->headers([
                                Header::make('hawb_number')->label('Số HAWB'),
                                Header::make('pcs')->label('Số PCS'),
                            ])
                            ->schema([
                                TextInput::make('hawb_number')
                                    ->label('Số HAWB')
                                    ->required()
                                    ->maxLength(255)
                                    ->reactive()
                                    ->helperText(fn () => $this->getHawbHelperText()),
                                TextInput::make('pcs')
                                    ->label('Số PCS')
                                    ->maxLength(255)
                            ])
                            ->addable(false)
                            ->reorderable(false)
                            ->emptyLabel('Chưa có HAWB nào được thêm')
                            ->minItems(1)
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

    protected function getListAgentApi(): array
    {
        try {
            $response = Http::timeout(10)->get('https://wh-nba.asgl.net.vn/api/list-agent');
            $data = $response->json();
            if (($data['success'] ?? false)) {
                $payload = $data['data'] ?? null;

                // Case: data is an array of strings: ["BOLO","APEX",...]
                if (is_array($payload) && !empty($payload) && is_string(array_values($payload)[0])) {
                    $result = [];
                    foreach ($payload as $agent) {
                        $result[$agent] = $agent;
                    }
                    return $result;
                }

                // Case: data is an associative array containing 'agents'
                if (is_array($payload) && isset($payload['agents']) && is_array($payload['agents'])) {
                    $agentsArr = $payload['agents'];
                    $result = [];
                    foreach ($agentsArr as $item) {
                        if (is_string($item)) {
                            $result[$item] = $item;
                        } elseif (is_array($item) && isset($item['AgentCode'], $item['AgentName'])) {
                            $result[$item['AgentCode']] = $item['AgentName'];
                        }
                    }
                    return $result;
                }

                // Case: data is an array of objects with AgentName / AgentCode
                if (is_array($payload) && !empty($payload) && is_array(reset($payload))) {
                    return collect($payload)->pluck('AgentName', 'AgentCode')->toArray();
                }
            }
        } catch (\Exception $e) {
            Log::error('Agent API error: ' . $e->getMessage());
        }

        return [];
    }


    protected function searchHawbApi(string $hawbNumber): array
    {
        // This endpoint requires authentication via the identity provider.
        $token = $this->getAuthToken();
        if (empty($token)) {
            Log::warning('No auth token available for HAWB check');
            return [];
        }

        try {
            // Use the check-in endpoint which accepts ?search=
            $url = "https://wh-nba.asgl.net.vn/api/check-in/hawb?search={$hawbNumber}";
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->timeout(10)->get($url);

            $data = $response->json();
            if (($data['success'] ?? false) && isset($data['data'])) {
                return $data['data'];
            }
        } catch (\Exception $e) {
            Log::error('HAWB API error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get cached auth token from identity provider. Cached for 1 hour.
     */
    protected function getAuthToken(): ?string
    {
        return Cache::remember('asgl_api_token', 3600, function () {
            try {
                $login = env('ASGL_API_LOGIN', 'ASGL-ĐKK');
                $password = env('ASGL_API_PASSWORD', 'Asgl@1909');

                $response = Http::timeout(10)->post('https://id.asgl.net.vn/api/auth/login', [
                    'login' => $login,
                    'password' => $password,
                ]);

                $data = $response->json();
                if (($data['success'] ?? false) && isset($data['data']['token'])) {
                    return $data['data']['token'];
                }
            } catch (\Exception $e) {
                Log::error('Auth token fetch error: ' . $e->getMessage());
            }

            return null;
        });
    }

    protected function fetchAndBindHawbs(string $search): void
    {
        $apiData = $this->searchHawbApi($search);
        // dd($apiData);
        // Expected shape: ['hawb' => [ {Hawb, Pcs, ...}, ... ]]
        $rows = [];
        if (isset($apiData['hawb']) && is_array($apiData['hawb'])) {
            foreach ($apiData['hawb'] as $item) {
                $rows[] = [
                    'hawb_number' => $item['Hawb'] ?? null,
                    'pcs' => isset($item['Pcs']) ? (string)$item['Pcs'] : null,
                ];
            }
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
        return view('livewire.registration-vehicle-form');
    }
}
