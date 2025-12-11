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
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\Actions\Action;
use Filament\Support\Enums\Alignment;
use App\Services\HawbService;
class RegistrationVehicleForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public ?string $searchMessage = null;
    public bool $hasSearched = false;

    public function mount(): void
    {
        $this->form->fill([
            'expected_in_at' => now()->format('Y-m-d H:i'),
        ]);
        
        // Load data from localStorage if available via JavaScript
        $this->dispatch('load-stored-data');
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
                            ->columnSpan([
                                'sm' => 2, // Mobile: full width
                                'md' => 1, // Desktop: half width
                            ]),

                        TextInput::make('driver_id_card')
                            ->label('Số CCCD/CMND')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan([
                                'sm' => 1, // Mobile: half width
                                'md' => 1, // Desktop: half width
                            ]),

                        TextInput::make('driver_phone')
                            ->label('Số điện thoại')
                            ->tel()
                            ->maxLength(20)
                            ->columnSpan([
                                'sm' => 1, // Mobile: half width
                                'md' => 1, // Desktop: half width
                            ]),

                        TextInput::make('vehicle_number')
                            ->label('Biển số xe')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan([
                                'sm' => 2, // Mobile: full width
                                'md' => 1, // Desktop: half width
                            ]),
                    ])
                    ->columns([
                        'sm' => 2, // Mobile: 2 columns
                        'md' => 2, // Desktop: 2 columns
                    ]),

                Section::make('Thông tin đơn vị và hàng hóa')
                    ->description('Vui lòng điền đầy đủ thông tin đơn vị và hàng hóa')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Select::make('name')
                            ->label('Tên đơn vị')
                            ->native(false)
                            ->options(HawbService::getListAgentApi())
                            // ->searchable()
                            ->columnSpan([
                                'sm' => 2, // Mobile: full width
                                'md' => 1, // Desktop: half width
                            ]),

                        TextInput::make('search_hawb')
                            ->label('Tìm kiếm HAWB')
                            ->suffixAction(function () {
                                return Action::make('search-hawb')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->action(function (Get $get) {
                                        $search = $get('search_hawb') ?? null;
                                        if ($search) {
                                            $this->fetchAndBindHawbs($search);
                                        }
                                    });
                            })
                            ->helperText(fn (Get $get) => $this->getSearchHelperText($get('search_hawb')))
                            ->columnSpan([
                                'sm' => 2, // Mobile: full width
                                'md' => 1, // Desktop: half width
                            ]),

                        TableRepeater::make('hawbs')
                            ->label('Danh sách HAWB')
                            ->columns(1)
                            ->headers([
                                Header::make('hawb_number')->label('Số HAWB'),
                                Header::make('pcs')->label('Số PCS')->width('120px')->align(Alignment::Center),
                            ])
                            ->schema([
                                TextInput::make('hawb_number')
                                    ->label('Số HAWB')
                                    ->required()
                                    ->maxLength(255)
                                    ->reactive(),
                                TextInput::make('pcs')
                                    ->label('Số PCS')
                                    ->maxLength(255)
                                    ->numeric()
                            ])
                            ->addable(false)
                            ->reorderable(false)
                            ->emptyLabel('Chưa có HAWB nào được thêm')
                            ->minItems(1)
                            ->columnSpanFull(),

                        DateTimePicker::make('expected_in_at')
                            ->label('Thời gian vào dự kiến')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('H:i d/m/Y')
                            ->columnSpan([
                                'sm' => 2, // Mobile: full width
                                'md' => 1, // Desktop: half width
                            ]),
                    ])
                    ->columns([
                        'sm' => 2, // Mobile: 2 columns
                        'md' => 2, // Desktop: 2 columns
                    ]),

                Textarea::make('notes')
                    ->label('Ghi chú')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    protected function fetchAndBindHawbs(string $search): void
    {
        $this->hasSearched = true;
        $apiData = HawbService::searchHawbApi($search);
        
        if ($apiData) {
            $newRows = HawbService::processHawbSearchResults($apiData);
            
            if (!empty($newRows)) {
                // Get existing hawbs or initialize empty array
                $existingHawbs = $this->data['hawbs'] ?? [];
                
                $result = HawbService::addHawbsToExisting($existingHawbs, $newRows);
                
                // Update the data
                $this->data['hawbs'] = $result['hawbs'];
                
                if ($result['added_count'] > 0) {
                    $this->searchMessage = "Đã thêm {$result['added_count']} HAWB mới (Tổng: {$result['total_count']})";
                } else {
                    $this->searchMessage = 'HAWB đã tồn tại trong danh sách';
                }
            } else {
                $this->searchMessage = 'Mã HAWB sai';
            }
        } else {
            $this->searchMessage = 'Mã HAWB sai';
        }
    }

    protected function getSearchHelperText(?string $searchValue): string
    {
        if (empty($searchValue)) {
            return 'Vui lòng nhập mã HAWB để tìm kiếm';
        }

        if (!$this->hasSearched) {
            return 'Nhấn nút tìm kiếm để lấy dữ liệu HAWB';
        }

        return $this->searchMessage ?? 'Nhấn nút tìm kiếm để lấy dữ liệu HAWB';
    }

    public function loadStoredDataFromJs($storedData): void
    {
        if (!empty($storedData)) {
            // Fill form with stored data (excluding HAWB data)
            $formData = [
                'driver_name' => $storedData['driver_name'] ?? '',
                'driver_phone' => $storedData['driver_phone'] ?? '',
                'driver_id_card' => $storedData['driver_id_card'] ?? '',
                'vehicle_number' => $storedData['vehicle_number'] ?? '',
                'name' => $storedData['name'] ?? '',
                'notes' => $storedData['notes'] ?? '',
                'expected_in_at' => now()->format('Y-m-d H:i'),
                'hawbs' => [], // Always reset HAWB to empty
                'search_hawb' => '', // Reset search field
            ];
            
            $this->form->fill($formData);
            
            // Reset search state
            $this->searchMessage = null;
            $this->hasSearched = false;
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
            ->where('expected_in_at', '>=', $newExpectedTime->copy()->subHours(4))
            ->where('expected_in_at', '<=', $newExpectedTime->copy()->addHours(4))
            ->orderBy('expected_in_at', 'desc')
            ->first();

        if ($existingRegistration) {
            Notification::make()
                ->title('Lỗi đăng ký')
                ->body('Đăng ký trước đó thành công rồi phải vào giờ khác (ít nhất cách 4 tiếng).')
                ->danger()
                ->duration(5000)
                ->send();
            return;
        }
        
        // Process data before saving
        $processedData = $data;
        
        // Encode hawbs array to JSON for hawb_number field
        if (isset($processedData['hawbs'])) {
            $processedData['hawb_number'] = json_encode($processedData['hawbs']);
            unset($processedData['hawbs']); // Remove hawbs array
        }
        
        // Remove search_hawb field as it's not needed in database
        unset($processedData['search_hawb']);
        
        $processedData['status'] = 'none';
        $record = RegistrationVehicle::create($processedData);

        // Gửi email và thông báo
        $this->sendEmailAndNotifications($record);

        Notification::make()
            ->title('Đăng ký thành công!')
            ->body('Đăng ký xe đã được tạo và gửi email thành công!')
            ->success()
            ->duration(5000)
            ->send();

        // Dispatch event for localStorage saving
        $this->dispatch('registration-success', $data);

        // Reset form - updated to match current structure
        $this->form->fill([
            'driver_name' => null,
            'name' => null,
            'driver_id_card' => null,
            'driver_phone' => null,
            'vehicle_number' => null,
            'search_hawb' => null,
            'hawbs' => [],
            'expected_in_at' => now()->format('Y-m-d H:i'),
            'notes' => null,
        ]);

        // Reset search state
        $this->searchMessage = null;
        $this->hasSearched = false;
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
