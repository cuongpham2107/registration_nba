<?php

namespace App\Livewire;

use App\Models\Area;
use App\Models\Invoice;
use App\Models\Registration;
use App\Models\User;
use App\Services\MailService;
use App\Services\RegistrationService;
use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;

class RegistrationGuestForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public bool $isListRegistered = false;

    public string $searchGuest = '';

    // Hold registrations to display in the list view
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

    public function updatedSearchGuest(): void
    {
        $this->loadRegistrations();
    }

    private function loadRegistrations(): void
    {
        $query = Registration::with('customers')
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('created_at', 'desc');

        if (! empty($this->searchGuest)) {
            $search = trim($this->searchGuest);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('bks', 'like', '%'.$search.'%')
                    ->orWhere('purpose', 'like', '%'.$search.'%')
                    ->orWhereHas('customers', function ($cq) use ($search) {
                        $cq->where('name', 'like', '%'.$search.'%')
                            ->orWhere('papers', 'like', '%'.$search.'%')
                            ->orWhere('license_plate', 'like', '%'.$search.'%');
                    });
            });
        }

        $rows = $query->limit(50)->get();

        $this->registrations = $rows->map(function (Registration $r) {
            $customerNames = $r->customers->pluck('name')->filter()->toArray();
            $customerPlates = $r->customers->pluck('license_plate')->filter()->unique()->toArray();

            $statusLabel = match (true) {
                $r->type === 'browse' => 'Đã phê duyệt',
                $r->type === 'refuse' => 'Từ chối',
                $r->status === 'sent' => 'Chờ phê duyệt',
                default => 'Chưa gửi',
            };

            $statusColorClass = match (true) {
                $r->type === 'browse' => 'bg-green-100 text-green-800',
                $r->type === 'refuse' => 'bg-red-100 text-red-800',
                $r->status === 'sent' => 'bg-yellow-100 text-yellow-800',
                default => 'bg-gray-100 text-gray-700',
            };

            $displayBks = $r->bks;
            if (empty($displayBks) && ! empty($customerPlates)) {
                $displayBks = implode(', ', $customerPlates);
            }

            return [
                'id' => $r->id,
                'name' => $r->name,
                'purpose' => $r->purpose,
                'bks' => $displayBks,
                'customers' => $customerNames,
                'start_date' => $r->start_date ? Carbon::parse($r->start_date)->format('d/m/Y H:i') : null,
                'end_date' => $r->end_date ? Carbon::parse($r->end_date)->format('d/m/Y H:i') : null,
                'status' => $r->status,
                'type' => $r->type,
                'status_label' => $statusLabel,
                'status_classes' => $statusColorClass,
            ];
        })->toArray();
    }

    public function mount(): void
    {
        $this->form->fill([
            'secret' => '',
            'name' => '',
            'bks' => '',
            'purpose' => '',
            'start_date' => now()->format('Y-m-d H:i'),
            'end_date' => now()->addHours(2)->format('Y-m-d H:i'),
            'asset' => '',
            'note' => '',
            'customers' => [
                [
                    'name' => '',
                    'papers' => '',
                    'type' => 'CCCD',
                    'areas' => [],
                    'license_plate' => '',
                    'note' => '',
                ],
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

                TextInput::make('name')
                    ->label('Đơn vị khách')
                    ->placeholder('Nhập tên công ty / đơn vị khách')
                    ->required()
                    ->live(onBlur: true)
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                $message = $this->validateOffensiveWords('name', $value, 'Đơn vị khách');
                                if ($message !== null) {
                                    $fail($message);
                                }
                            };
                        },
                    ])
                    ->validationMessages([
                        'required' => 'Đơn vị khách không được để trống.',
                    ])
                    ->extraAttributes([
                        'class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600',
                    ])
                    ->maxLength(255)
                    ->columnSpan(2),

                TextInput::make('bks')
                    ->label('BKS ô tô')
                    ->placeholder('Ví dụ: 29C-12345')
                    ->prefixIcon('heroicon-o-truck')
                    ->live(onBlur: true)
                    ->extraAttributes([
                        'class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600',
                    ])
                    ->maxLength(255)
                    ->columnSpan(2),

                Textarea::make('purpose')
                    ->label('Mục đích')
                    ->placeholder('Nhập mục đích làm việc / vào kho')
                    ->required()
                    ->rows(2)
                    ->live(onBlur: true)
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                $message = $this->validateOffensiveWords('purpose', $value, 'Mục đích');
                                if ($message !== null) {
                                    $fail($message);
                                }
                            };
                        },
                    ])
                    ->validationMessages([
                        'required' => 'Mục đích không được để trống.',
                    ])
                    ->extraAttributes([
                        'class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600',
                    ])
                    ->maxLength(255)
                    ->columnSpan(2),

                DateTimePicker::make('start_date')
                    ->label('Giờ vào dự kiến')
                    ->placeholder('Chọn ngày, giờ vào dự kiến')
                    ->required()
                    ->native(true)
                    ->prefixIcon('heroicon-o-calendar')
                    ->seconds(false)
                    ->displayFormat('H:i d/m/Y')
                    ->extraAttributes([
                        'class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600',
                    ])
                    ->columnSpan([
                        'default' => 2,
                        'sm' => 1,
                    ]),

                DateTimePicker::make('end_date')
                    ->label('Giờ ra dự kiến')
                    ->placeholder('Chọn ngày, giờ ra dự kiến')
                    ->required()
                    ->native(true)
                    ->prefixIcon('heroicon-o-calendar')
                    ->seconds(false)
                    ->displayFormat('H:i d/m/Y')
                    ->rules([
                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                            if ($value && $get('start_date')) {
                                $start = Carbon::parse($get('start_date'), 'Asia/Ho_Chi_Minh');
                                $end = Carbon::parse($value, 'Asia/Ho_Chi_Minh');
                                if ($end->isBefore($start)) {
                                    $fail('Giờ ra dự kiến phải sau giờ vào dự kiến.');
                                }
                            }
                        },
                    ])
                    ->validationMessages([
                        'required' => 'Giờ ra dự kiến không được để trống.',
                    ])
                    ->extraAttributes([
                        'class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600',
                    ])
                    ->columnSpan([
                        'default' => 2,
                        'sm' => 1,
                    ]),

                Repeater::make('customers')
                    ->label('Danh sách khách')
                    ->itemLabel(function (array $state): ?string {
                        if (! empty($state['name'])) {
                            return $state['name'].(! empty($state['papers']) ? ' ('.$state['papers'].')' : '');
                        }

                        return 'Khách mới';
                    })
                    ->collapsible()
                    ->cloneable(false)
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            TextInput::make('name')
                                ->label('Tên khách')
                                ->placeholder('Họ và tên')
                                ->required()
                                ->validationMessages([
                                    'required' => 'Tên khách không được để trống.',
                                ])
                                ->extraAttributes(['class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600'])
                                ->maxLength(255)
                                ->columnSpan(1),

                            TextInput::make('papers')
                                ->label('Số giấy tờ')
                                ->placeholder('Số CCCD/CMND/Passport')
                                ->required()
                                ->validationMessages([
                                    'required' => 'Số giấy tờ không được để trống.',
                                ])
                                ->extraAttributes(['class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600'])
                                ->maxLength(255)
                                ->columnSpan(1),

                            Select::make('type')
                                ->label('Loại giấy tờ')
                                ->options([
                                    'CCCD' => 'CCCD',
                                    'CMND' => 'CMND',
                                    'Hộ chiếu' => 'Hộ chiếu',
                                    'Bằng lái xe' => 'Bằng lái xe',
                                    'Khác' => 'Khác',
                                ])
                                ->default('CCCD')
                                ->required()
                                ->native(false)
                                ->extraAttributes(['class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600'])
                                ->columnSpan(1),

                            TextInput::make('license_plate')
                                ->label('Biển số xe cá nhân')
                                ->placeholder('BKS xe máy/ô tô (nếu có)')
                                ->extraAttributes(['class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600'])
                                ->maxLength(255)
                                ->columnSpan(1),

                            Select::make('areas')
                                ->label('Khu vực')
                                ->multiple()
                                ->options(Area::all()->pluck('name', 'code'))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->validationMessages([
                                    'required' => 'Vui lòng chọn ít nhất 1 khu vực.',
                                ])
                                ->extraAttributes(['class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600'])
                                ->columnSpan([
                                    'default' => 1,
                                    'sm' => 2,
                                ]),

                            TextInput::make('note')
                                ->label('Ghi chú')
                                ->placeholder('Ghi chú khách (nếu có)')
                                ->extraAttributes(['class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600'])
                                ->maxLength(255)
                                ->columnSpan([
                                    'default' => 1,
                                    'sm' => 2,
                                ]),
                        ]),
                    ])
                    ->addActionLabel('Thêm khách')
                    ->reorderable(false)
                    ->minItems(1)
                    ->defaultItems(1)
                    ->columnSpan(2),

                Textarea::make('asset')
                    ->label('Tài sản mang vào/ra')
                    ->placeholder('Thiết bị, máy móc, tài sản mang theo (nếu có)')
                    ->rows(2)
                    ->live(onBlur: true)
                    ->extraAttributes(['class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600'])
                    ->maxLength(255)
                    ->columnSpan(2),

                Textarea::make('note')
                    ->label('Ghi chú chung')
                    ->placeholder('Thông tin ghi chú thêm (nếu có)')
                    ->rows(2)
                    ->live(onBlur: true)
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                $message = $this->validateOffensiveWords('note', $value, 'Ghi chú');
                                if ($message !== null) {
                                    $fail($message);
                                }
                            };
                        },
                    ])
                    ->extraAttributes(['class' => '!bg-gray-100 dark:!bg-gray-700 dark:!text-white dark:!border-gray-600'])
                    ->maxLength(255)
                    ->columnSpan(2),
            ])
            ->statePath('data');
    }

    public function loadStoredDataFromJs($storedData): void
    {
        if (! empty($storedData)) {
            $formData = [
                'secret' => $storedData['secret'] ?? '',
                'name' => $storedData['name'] ?? '',
                'bks' => $storedData['bks'] ?? '',
                'purpose' => $storedData['purpose'] ?? '',
                'asset' => $storedData['asset'] ?? '',
                'note' => $storedData['note'] ?? '',
                'start_date' => now()->format('Y-m-d H:i'),
                'end_date' => now()->addHours(2)->format('Y-m-d H:i'),
                'customers' => ! empty($storedData['customers']) ? $storedData['customers'] : [
                    [
                        'name' => '',
                        'papers' => '',
                        'type' => 'CCCD',
                        'areas' => [],
                        'license_plate' => '',
                        'note' => '',
                    ],
                ],
            ];

            $this->form->fill($formData);
        }
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $processedData = $data;

        // Normalize license plate
        if (! empty($processedData['bks'])) {
            $processedData['bks'] = Invoice::normalizeLicensePlate($processedData['bks']);
        }

        $processedData['secret'] = trim((string) ($processedData['secret'] ?? ''));
        if ($processedData['secret'] === '') {
            $processedData['secret'] = Str::random(12);
        }
        $data['secret'] = $processedData['secret'];

        // Find default user registration@gmail.com
        $defaultUser = User::where('email', 'registration@gmail.com')
            ->orWhere('name', 'registration@gmail.com')
            ->first();

        if (! $defaultUser) {
            $defaultUser = User::first();
        }

        if (! $defaultUser) {
            Notification::make()
                ->title('Lỗi hệ thống')
                ->body('Không tìm thấy tài khoản người dùng mặc định trên hệ thống.')
                ->danger()
                ->send();

            return;
        }

        // Prepare registration record data
        $registrationData = [
            'name' => $processedData['name'],
            'purpose' => $processedData['purpose'] ?? null,
            'bks' => $processedData['bks'] ?? null,
            'start_date' => $processedData['start_date'],
            'end_date' => $processedData['end_date'],
            'status' => 'sent',
            'type' => null,
            'asset' => $processedData['asset'] ?? null,
            'note' => $processedData['note'] ?? null,
            'user_id' => $defaultUser->id,
        ];

        // Create main registration record
        $registration = Registration::create($registrationData);

        // Create customers
        if (! empty($data['customers']) && is_array($data['customers'])) {
            foreach ($data['customers'] as $customerData) {
                if (! empty($customerData['name']) && ! empty($customerData['papers'])) {
                    $licensePlate = ! empty($customerData['license_plate'])
                        ? Invoice::normalizeLicensePlate($customerData['license_plate'])
                        : null;

                    $registration->customers()->create([
                        'name' => $customerData['name'],
                        'papers' => $customerData['papers'],
                        'type' => $customerData['type'] ?? 'CCCD',
                        'areas' => $customerData['areas'] ?? [],
                        'license_plate' => $licensePlate,
                        'note' => $customerData['note'] ?? null,
                    ]);
                }
            }
        }

        // Send mail and notifications to approvers of default user
        try {
            (new RegistrationService)->sendMailForRegistration($registration);
        } catch (\Throwable $e) {
            Log::error('Send mail for registration guest failed: '.$e->getMessage());
        }

        // Dispatch event for localStorage saving before redirect
        $this->dispatch('registration-success', [
            'name' => $data['name'] ?? '',
            'bks' => $data['bks'] ?? '',
            'purpose' => $data['purpose'] ?? '',
            'asset' => $data['asset'] ?? '',
            'note' => $data['note'] ?? '',
            'customers' => $data['customers'] ?? [],
            'secret' => $data['secret'],
        ]);

        // Store registration data in session for success page
        session()->flash('registration_guest_data', [
            'id' => $registration->id,
            'name' => $registration->name,
            'purpose' => $registration->purpose,
            'bks' => $registration->bks,
            'start_date' => $registration->start_date,
            'end_date' => $registration->end_date,
            'asset' => $registration->asset,
            'note' => $registration->note,
            'customers' => $registration->customers()->get()->toArray(),
        ]);

        // Redirect to success page
        $this->redirect(route('registration-guest.success'), navigate: true);
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
        return view('livewire.registration-guest-form');
    }
}
