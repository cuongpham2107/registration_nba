<?php

use App\Models\Company;
use App\Models\RegistrationEntry;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;
use Livewire\Component;

new class extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public int $registrationEntryId;

    public ?array $data = [];

    public ?string $taxInfoError = null;

    private ?string $lastFetchedTaxCode = null;

    public bool $isAlreadyIssued = false;

    public function mount(int $id)
    {
        $this->registrationEntryId = $id;

        $registrationEntry = RegistrationEntry::query()->with('invoice')->find($this->registrationEntryId);
        // dd($registrationEntry);
        if ($registrationEntry && $registrationEntry->invoice && $registrationEntry->invoice->company_id) {
            $this->isAlreadyIssued = true;
        }

        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tax_code')
                    ->required()
                    ->label('Mã số thuế')
                    ->live(onBlur: true)
                    ->hint(new HtmlString('<span wire:loading wire:target="data.tax_code" class="text-sm text-gray-500 animate-pulse">Đang lấy thông tin...</span>'))
                    ->afterStateUpdated(function ($state): void {
                        $this->fetchBusinessInfoByTaxCode($state);
                    }),
                TextInput::make('name')
                    ->label('Tên công ty')
                    ->required(),
                Grid::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->label('Email'),
                        TextInput::make('phone')
                            ->label('Số điện thoại'),
                    ]),
                TextInput::make('address')
                    ->label('Địa chỉ'),
            ])
            ->statePath('data');
    }

    private function fetchBusinessInfoByTaxCode(?string $taxCode): void
    {
        $taxCode = preg_replace('/\D+/', '', (string) $taxCode);

        $this->taxInfoError = null;

        if ($taxCode === '') {
            return;
        }

        // Tránh gọi API khi MST quá ngắn.
        if (strlen($taxCode) < 10) {
            return;
        }

        // Tránh gọi lại nếu user blur nhiều lần nhưng MST không đổi.
        if ($this->lastFetchedTaxCode === $taxCode) {
            return;
        }

        try {
            $request = fn () => Http::timeout(8)
                ->withHeaders([
                    // Giống request curl đang OK trên server.
                    'Accept' => '*/*',
                ])
                ->get("https://api.vietqr.io/v2/business/{$taxCode}");

            $response = $request();
            $json = $response->json();
            $data = $json['data'] ?? null;

            if (! is_array($data)) {
                $this->taxInfoError = (string) ($json['desc'] ?? 'Không tìm thấy thông tin mã số thuế.');

                return;
            }

            $currentData = $this->data;

            $this->data['tax_code'] = $data['id'];
            $this->data['name'] = $data['name'] ?? ($currentData['name'] ?? null);
            $this->data['address'] = $data['address'] ?? ($currentData['address'] ?? null);

            $this->lastFetchedTaxCode = $taxCode;

            $this->form->fill($this->data);
        } catch (Throwable $e) {
            $this->taxInfoError = 'Lỗi khi gọi API VietQR: '.$e->getMessage();
        }
    }

    public function create(): void
    {
        try {
            $company = Company::updateOrCreate(
                ['tax_code' => $this->data['tax_code']],
                [
                    'name' => $this->data['name'] ?? null,
                    'address' => $this->data['address'] ?? null,
                    'email' => $this->data['email'] ?? null,
                    'phone' => $this->data['phone'] ?? null,
                ]
            );

            $registrationEntry = RegistrationEntry::query()->with('invoice')->find($this->registrationEntryId);

            if (! $registrationEntry || ! $registrationEntry->invoice) {
                Notification::make()
                    ->title('Không tìm thấy hóa đơn liên quan')
                    ->danger()
                    ->send();

                return;
            }

            $registrationEntry->invoice->update([
                'company_id' => $company->id,
                'is_issued' => true,
            ]);

            $this->isAlreadyIssued = true;

            Notification::make()
                ->title('Thông tin công ty đã được lưu')
                ->success()
                ->send();

        } catch (Throwable $e) {
            Notification::make()
                ->title('Đã xảy ra lỗi khi lưu')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
};
?>
<div class="min-h-screen bg-[#5287ad] py-1 px-1 sm:px-3 lg:px-4 flex items-center justify-center">
    <div class="max-w-md w-full mx-auto">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col w-full h-screen justify-between" >
            
            @if($isAlreadyIssued)
            <!-- Trạng thái đã lưu thông tin -->
            <div class="flex-1 flex flex-col items-center justify-center p-6 text-center h-full">
                <div class="mb-8">
                    <img src="{{ asset('images/ASG.png') }}" alt="ASG Logo" class="h-10 w-auto mx-auto filter drop-shadow-sm">
                </div>
                <div class="w-24 h-24 bg-green-50 rounded-full flex items-center justify-center mb-6 mx-auto shadow-sm border border-green-100">
                    <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-3">Thông tin đã được lưu</h1>
                <p class="text-gray-600 dark:text-gray-400 text-base leading-relaxed">Hóa đơn này đã có đầy đủ thông tin công ty để xuất hóa đơn.</p>
            </div>
            @else
            <!-- Trạng thái form nhập định danh -->
             <div>
                <div class=" px-1 pt-2 text-center">
                    <div class="flex justify-center mb-3">
                        <img src="{{ asset('images/ASG.png') }}" alt="ASG Logo" class="h-8 w-24">
                    </div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Thông tin công ty cần xuất hoá đơn</h1>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Vui lòng cung cấp thông tin chính xác để chúng tôi có thể xuất hoá đơn đúng yêu cầu của bạn.</p>
                </div>
            </div>
            <!-- Main Content Area -->
             <div class="flex-1 overflow-y-auto">
                 <div class="px-3 py-2">
                    <form wire:submit="create">
                        {{ $this->form }}
                        @if ($taxInfoError)
                            <div class="mt-2 text-sm text-red-600">{{ $taxInfoError }}</div>
                        @endif
                        <div class="flex gap-4" style="margin-top: 16px;"></div>
                        <button class="flex-1 w-full bg-linear-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
                                 style="background: linear-gradient(45deg, #10b981, #059669); color: white; padding: 12px 24px; border-radius: 8px; border: none; font-weight: 600; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); transition: all 0.2s; cursor: pointer;"
                                 onmouseover="this.style.background='linear-gradient(45deg, #059669, #047857)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(0, 0, 0, 0.15)'"
                                 onmouseout="this.style.background='linear-gradient(45deg, #10b981, #059669)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)'"
                                 type="submit">
                            Gửi
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
    <x-filament-actions::modals />
</div>