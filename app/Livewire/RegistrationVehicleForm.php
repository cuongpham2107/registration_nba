<?php

namespace App\Livewire;

use App\Forms\Components\AutocompleteHawb;
use App\Models\RegistrationVehicle;
use App\Models\User;
use App\Services\HawbService;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;

class RegistrationVehicleForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public bool $isListRegistered = false;

    public string $searchDriver = '';

    public ?int $exitedFeeId = null;

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
        $query = RegistrationVehicle::whereDate('created_at', now()->toDateString())
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

        $this->registrations = $rows->map(function (RegistrationVehicle $r) {
            // Decode hawb list if present
            $hawbList = [];
            if (! empty($r->hawb_number)) {
                $decoded = json_decode($r->hawb_number, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $item) {
                        if (! empty($item['hawb_number'])) {
                            $hawbList[] = $item['hawb_number'];
                        }
                    }
                }
            }

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
                'hawbs' => $hawbList,
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
            'secret' => '',
            'expected_in_at' => now()->format('Y-m-d H:i'),
            'hawbs' => [
                ['hawb_number' => null, 'pcs' => null],
            ],
        ]);

        // Load data from localStorage if available via JavaScript
        $this->dispatch('load-stored-data');
    }

    public function form(Form $form): Form
    {
        return $form
            ->extraAttributes(['style' => 'gap: 0.5rem;', 'class' => 'bg-white dark:bg-gray-800'])
            ->columns(2)
            ->schema([
                Hidden::make('secret'),

                TextInput::make('driver_name')
                    ->label('Tên tài xế')
                    ->required()
                    ->live(onBlur: true)
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                $message = $this->validateOffensiveWords('driver_name', $value, 'Tên tài xế');

                                if ($message !== null) {
                                    $fail($message);
                                }
                            };
                        },
                    ])
                    ->validationMessages([
                        'required' => 'Tên tài xế không được để trống.',
                        'blacklist' => 'Tên tài xế chứa từ không hợp lệ.',
                    ])
                    ->extraAttributes([
                        'class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600',
                    ])
                    ->maxLength(255)
                    ->columnSpan(2),

                TextInput::make('driver_id_card')
                    ->label('Số CCCD/CMND')
                    ->required()
                    ->live(onBlur: true)
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                if ($this->isFieldBlacklisted('driver_id_card', $value)) {
                                    $fail('Số CCCD/CMND này đang nằm trong danh sách đen.');
                                }
                            };
                        },
                    ])
                    ->validationMessages([
                        'required' => 'Số CCCD/CMND không được để trống.',
                    ])
                    ->extraAttributes(['class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600'])
                    ->maxLength(255)
                    ->columnSpan(1),

                TextInput::make('driver_phone')
                    ->label('Số điện thoại')
                    ->tel()
                    ->live(onBlur: true)
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                if ($this->isFieldBlacklisted('driver_phone', $value)) {
                                    $fail('Số điện thoại này đang nằm trong danh sách đen.');
                                }
                            };
                        },
                    ])
                    ->extraAttributes(['class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600'])
                    ->maxLength(20)
                    ->required()
                    ->columnSpan(1),

                TextInput::make('vehicle_number')
                    ->label('Biển số xe')
                    ->required()
                    ->live(onBlur: true)
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                if ($this->isFieldBlacklisted('vehicle_number', $value)) {
                                    $fail('Biển số xe này đang nằm trong danh sách đen.');
                                }
                            };
                        },
                    ])
                    ->afterStateUpdated(function (?string $state, callable $set) {
                        if (empty($state)) {
                            $set('fee_id', null);
                            $this->exitedFeeId = null;
                            return;
                        }

                        $normalized = \App\Models\Invoice::normalizeLicensePlate($state);
                        $exited = \App\Models\RegistrationVehicle::where('vehicle_number', $normalized)
                            ->where('status', 'exited')
                            ->latest()
                            ->first();

                        if ($exited && $exited->fee_id) {
                            $set('fee_id', $exited->fee_id);
                            $this->exitedFeeId = $exited->fee_id;
                        } else {
                            $set('fee_id', null);
                            $this->exitedFeeId = null;
                        }
                    })
                    ->extraAttributes(['class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600'])
                    ->validationMessages([
                        'required' => 'Biển số xe không được để trống.',
                    ])
                    ->maxLength(255)
                    ->columnSpan(2),
                Select::make('fee_id')
                    ->label('Loại xe, trọng tải')
                    ->options(\App\Models\Fee::pluck('ticket_code', 'id'))
                    ->required()
                    ->disabled(fn () => $this->exitedFeeId !== null)
                    ->dehydrated()
                    ->extraAttributes(['class' => '!bg-gray-100'])
                    ->columnSpan(2)
                    ->native(false),
                Select::make('name')
                    ->label('Tên đơn vị')
                    ->native(false)
                    ->multiple()
                    ->extraAttributes(['class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600'])
                    ->options(HawbService::getListAgentApi())
                    ->required()
                    ->validationMessages([
                        'required' => 'Tên đơn vị không được để trống.',
                    ])
                    ->columnSpan(2),

                TableRepeater::make('hawbs')
                    ->label(new \Illuminate\Support\HtmlString('Danh sách HAWB <br><span class="text-[10px] italic text-blue-600 dark:text-blue-400">(Nhập 5 số cuối của số hawb. Sau đó chọn số Hawb từ danh sách gợi ý)</span>'))
                    ->headers([
                        Header::make('hawb_number')->label('Số HAWB'),
                        Header::make('pcs')->label('Số PCS')->width('100px')->align(Alignment::Center),
                    ])
                    ->schema([
                        AutocompleteHawb::make('hawb_number')
                            ->label('Số HAWB')
                            ->required()
                            ->validationMessages([
                                'required' => 'Chưa chọn số HAWB hợp lệ.',
                            ])
                            ->extraAttributes(['class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600 rounded-lg'])
                            ->live(onBlur: true)
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        if (empty($value)) {
                                            $fail('Vui lòng chọn một HAWB từ danh sách.');

                                            return;
                                        }

                                        // Kiểm tra xem HAWB có tồn tại trong API không
                                        try {
                                            $apiData = HawbService::searchHawbApi($value);
                                            if (! $apiData || ! isset($apiData['hawb']) || ! is_array($apiData['hawb'])) {
                                                $fail('HAWB này không tồn tại trong hệ thống. Vui lòng chọn từ danh sách gợi ý.');

                                                return;
                                            }

                                            // Kiểm tra xem có HAWB nào khớp chính xác không
                                            $found = false;
                                            foreach ($apiData['hawb'] as $item) {
                                                if (! empty($item['Hawb']) && $item['Hawb'] === $value) {
                                                    $found = true;
                                                    break;
                                                }
                                            }

                                            if (! $found) {
                                                $fail('HAWB này không tồn tại trong hệ thống. Vui lòng chọn từ danh sách gợi ý.');
                                            }
                                        } catch (\Exception $e) {
                                            $fail('Không thể xác thực HAWB. Vui lòng thử lại.');
                                        }
                                    };
                                },
                            ])
                            ->afterStateUpdated(function (?string $state, callable $set) {
                                // When a HAWB is selected, fetch its details and set pcs
                                if (empty($state)) {
                                    $set('pcs', null);

                                    return;
                                }

                                try {
                                    if (strlen($state) >= 5) {
                                        $apiData = HawbService::searchHawbApi($state);
                                        if ($apiData && isset($apiData['hawb']) && is_array($apiData['hawb'])) {
                                            // Find the exact hawb item
                                            foreach ($apiData['hawb'] as $item) {
                                                if (! empty($item['Hawb']) && $item['Hawb'] === $state) {
                                                    $pcs = $item['Pcs'] ?? null;
                                                    // Ensure numeric where possible
                                                    if (is_numeric($pcs)) {
                                                        $set('pcs', (int) $pcs);
                                                    } else {
                                                        $set('pcs', $pcs);
                                                    }
                                                    // Clear previous validation errors for HAWB fields
                                                    if (method_exists($this, 'resetValidation')) {
                                                        // reset only hawb_number validation(s) under the form state
                                                        $this->resetValidation(['data.hawbs.*.hawb_number']);
                                                    }

                                                    return;
                                                }
                                            }
                                        }
                                    }
                                    // fallback: clear pcs
                                    $set('pcs', null);
                                    // $set('hawb_number', null);
                                } catch (\Exception $e) {
                                    // On error, do not break the form; just clear pcs
                                    $set('pcs', null);
                                    // $set('hawb_number', null);
                                }
                            }),

                        TextInput::make('pcs')
                            ->label('Số PCS')
                            ->extraAttributes(['class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600'])
                            ->numeric()
                            ->minValue(1),
                    ])
                    ->reorderable(false)
                    ->emptyLabel('Chưa có HAWB nào được thêm')
                    ->addAction(callback: function (Action $action) {
                        return $action->label('Thêm HAWB Mới')->icon('heroicon-o-plus')->size(ActionSize::ExtraSmall)
                            ->extraAttributes(['class' => '-mt-2']);
                    })
                    ->minItems(1)
                    ->defaultItems(1)
                    ->columnSpan(2),

                DateTimePicker::make('expected_in_at')
                    ->label('Thời gian vào dự kiến')
                    ->placeholder('Chọn ngày, giờ vào dự kiến')
                    ->required()
                    ->native(true)
                    ->prefixIcon('heroicon-o-calendar')
                    ->extraAttributes(['class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600'])
                    ->seconds(false)
                    ->displayFormat('H:i d/m/Y')
                    ->columnSpan(2),

                Textarea::make('notes')
                    ->label('Ghi chú')
                    ->rows(2)
                    ->live(onBlur: true)
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                $message = $this->validateOffensiveWords('notes', $value, 'Ghi chú');

                                if ($message !== null) {
                                    $fail($message);
                                }
                            };
                        },
                    ])
                    ->validationMessages([
                        'blacklist' => 'Ghi chú chứa từ không hợp lệ.',
                    ])
                    ->extraAttributes(['class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600'])
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
                'secret' => $storedData['secret'] ?? '',
                'driver_name' => $storedData['driver_name'] ?? '',
                'driver_phone' => $storedData['driver_phone'] ?? '',
                'driver_id_card' => $storedData['driver_id_card'] ?? '',
                // 'vehicle_number' => $storedData['vehicle_number'] ?? '',
                'vehicle_number' => null,
                // 'name' => $storedData['name'] ?? '',
                'name' => null,
                'notes' => $storedData['notes'] ?? '',
                'expected_in_at' => now()->format('Y-m-d H:i'),
                // Keep default hawbs row - always start fresh with 1 empty row
                'hawbs' => [
                    ['hawb_number' => null, 'pcs' => null],
                ],
            ];

            $this->form->fill($formData);
        }
    }

    public function create(): void
    {
        $data = $this->form->getState();

        // Process data before saving
        $processedData = $data;

        // Normalize license plate
        if (! empty($processedData['vehicle_number'])) {
            $processedData['vehicle_number'] = \App\Models\Invoice::normalizeLicensePlate($processedData['vehicle_number']);
        }

        $processedData['secret'] = trim((string) ($processedData['secret'] ?? ''));
        if ($processedData['secret'] === '') {
            $processedData['secret'] = Str::random(12);
        }
        $data['secret'] = $processedData['secret'];

        $blacklistedFields = $this->getBlacklistedFields($processedData);
        if (! empty($blacklistedFields)) {
            foreach ($blacklistedFields as $field => $message) {
                $this->addError("data.{$field}", $message);
            }

            Notification::make()
                ->title('Lỗi đăng ký')
                ->body('Thông tin tài xế/xe thuộc danh sách đen, không thể đăng ký.')
                ->danger()
                ->color('danger')
                ->duration(8000)
                ->send();

            return;
        }

        // Convert name array to comma-separated string
        if (isset($processedData['name']) && is_array($processedData['name'])) {
            $processedData['name'] = implode(', ', $processedData['name']);
        }

        // Encode hawbs array to JSON for hawb_number field
        if (isset($processedData['hawbs'])) {
            $processedData['hawb_number'] = json_encode($processedData['hawbs']);
            unset($processedData['hawbs']); // Remove hawbs array
        }

        // Remove search_hawb field as it's not needed in database
        unset($processedData['search_hawb']);

        // Kiểm tra trùng lặp với điều kiện thời gian cách nhau ít nhất 30 phút
        $newExpectedTime = Carbon::parse($processedData['expected_in_at']);

        // Decode hawb_number để lấy danh sách HAWB numbers
        $newHawbs = json_decode($processedData['hawb_number'], true);
        $newHawbNumbers = [];
        if (is_array($newHawbs)) {
            foreach ($newHawbs as $hawb) {
                if (! empty($hawb['hawb_number'])) {
                    $newHawbNumbers[] = $hawb['hawb_number'];
                }
            }
        }

        $existingRegistrations = RegistrationVehicle::where('name', $processedData['name'])
            ->where('driver_name', $processedData['driver_name'])
            ->where('driver_phone', $processedData['driver_phone'])
            ->where('driver_id_card', $processedData['driver_id_card'])
            ->where('vehicle_number', $processedData['vehicle_number'])
            ->orderBy('expected_in_at', 'desc')
            ->get();

        foreach ($existingRegistrations as $existingRegistration) {
            // Decode existing hawb_number
            $existingHawbs = json_decode($existingRegistration->hawb_number, true);
            $existingHawbNumbers = [];
            if (is_array($existingHawbs)) {
                foreach ($existingHawbs as $hawb) {
                    if (! empty($hawb['hawb_number'])) {
                        $existingHawbNumbers[] = $hawb['hawb_number'];
                    }
                }
            }

            // Kiểm tra xem có ít nhất 1 HAWB trùng nhau không (intersection)
            $duplicateHawbs = array_intersect($newHawbNumbers, $existingHawbNumbers);

            if (! empty($duplicateHawbs)) {
                $existingTime = Carbon::parse($existingRegistration->expected_in_at);
                $minutesDifference = $newExpectedTime->diffInMinutes($existingTime, false);

                // Kiểm tra nếu thời gian mới không cách thời gian cũ ít nhất 30 phút
                if (abs($minutesDifference) < 30) {
                    $duplicateList = implode(', ', $duplicateHawbs);
                    Notification::make()
                        ->title('Lỗi đăng ký')
                        ->body("Các HAWB sau đã được đăng ký trước đó: {$duplicateList}. Vui lòng đăng ký vào giờ khác (ít nhất cách 30 phút).")
                        ->danger()
                        ->color('danger')
                        ->duration(10000)
                        ->send();

                    return;
                }
            }
        }

        $processedData['status'] = 'sent';
        $record = RegistrationVehicle::create($processedData);

        // Gửi email và thông báo
        // $this->sendEmailAndNotifications($record);

        // Dispatch event for localStorage saving before redirect
        $this->dispatch('registration-success', $data);

        // Redirect to success page with registration data
        $this->redirect(route('registration-vehicle.success'), navigate: true);

        // Store registration data in session for success page
        session()->flash('registration_data', [
            'driver_name' => $record->driver_name,
            'name' => $record->name,
            'vehicle_number' => $record->vehicle_number,
            'hawb_number' => $record->hawb_number,
            'pcs' => $record->pcs,
            'expected_in_at' => $record->expected_in_at,
        ]);
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
                    $mail = (new \App\Services\MailService)->sendMailWithTemplate(
                        $user->email,
                        'Đăng ký xe khai thác: '.$record->driver_name.' | '.$record->vehicle_number.' | '.date('Y-m-d H:i:s'),
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
            Log::error('Failed to send notifications: '.$e->getMessage());
        }
    }

    private function isFieldBlacklisted(string $field, $value): bool
    {
        if (! in_array($field, ['vehicle_number', 'driver_phone', 'driver_id_card'], true)) {
            return false;
        }

        // Check 1: Field value blacklist — biển số/CCCD/SĐT cụ thể bị blacklist
        $normalizedValue = $this->normalizeFieldValueForBlacklist($field, $value);

        if ($normalizedValue !== '') {
            if (RegistrationVehicle::query()
                ->where($field, $normalizedValue)
                ->whereHas('blacklist', fn ($q) => $q->where('is_active', true))
                ->exists()
            ) {
                return true;
            }
        }

        // Check 2: Browser secret blacklist — device ban (chặn cả khi nhập sai thông tin)
        $browserSecret = $this->currentBrowserSecret();

        if ($browserSecret !== '') {
            if (RegistrationVehicle::query()
                ->where('secret', $browserSecret)
                ->whereHas('blacklist', fn ($q) => $q->where('is_active', true))
                ->exists()
            ) {
                return true;
            }
        }

        return false;
    }

    private function currentBrowserSecret(): string
    {
        return trim((string) data_get($this->data, 'secret', ''));
    }

    private function normalizeFieldValueForBlacklist(string $field, $value): string
    {
        $normalizedValue = trim((string) $value);

        if ($normalizedValue === '') {
            return '';
        }

        if ($field === 'vehicle_number') {
            return \App\Models\Invoice::normalizeLicensePlate($normalizedValue);
        }

        if ($field === 'driver_phone') {
            return preg_replace('/\D+/', '', $normalizedValue) ?? '';
        }

        if ($field === 'driver_id_card') {
            return strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $normalizedValue) ?? '');
        }

        // Default: normalize text fields (names, notes) to lowercase and collapse whitespace
        $normalizedValue = mb_strtolower($normalizedValue);
        $normalizedValue = preg_replace('/\s+/u', ' ', $normalizedValue) ?? $normalizedValue;

        return $normalizedValue;
    }

    private function getBlacklistedFields(array $processedData): array
    {
        $fieldLabels = [
            'driver_id_card' => 'Số CCCD/CMND',
            'driver_phone' => 'Số điện thoại',
            'vehicle_number' => 'Biển số xe',
        ];

        $errors = [];

        foreach ($fieldLabels as $field => $label) {
            if ($this->isFieldBlacklisted($field, $processedData[$field] ?? null)) {
                $errors[$field] = "{$label} đang nằm trong danh sách đen.";
            }
        }

        return $errors;
    }

    private function validateOffensiveWords(string $field, $value, string $label): ?string
    {
        $normalizedValue = $this->normalizeOffensiveInput($value);

        if ($normalizedValue === '') {
            return null;
        }

        $badWords = config('offensive-word.bad_words', []);

        foreach ($badWords as $badWord) {
            $normalizedBadWord = $this->normalizeOffensiveInput($badWord);

            if ($normalizedBadWord === '') {
                continue;
            }

            $pattern = '/(?<!\\p{L})'.preg_quote($normalizedBadWord, '/').'(?!\\p{L})/u';

            if (preg_match($pattern, $normalizedValue) === 1) {
                return "{$label} chứa từ không phù hợp.";
            }
        }

        return null;
    }

    private function normalizeOffensiveInput($value): string
    {
        $normalizedValue = trim((string) $value);

        if ($normalizedValue === '') {
            return '';
        }

        $normalizedValue = mb_strtolower($normalizedValue);
        $normalizedValue = preg_replace('/\s+/u', ' ', $normalizedValue) ?? $normalizedValue;

        return $normalizedValue;
    }

    public function render()
    {
        return view('livewire.registration-vehicle-form');
    }
}
