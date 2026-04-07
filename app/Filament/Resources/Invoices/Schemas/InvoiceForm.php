<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Invoice;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin hóa đơn')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_code')
                            ->label('Mã hóa đơn')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Tự động tạo nếu để trống')
                            ->default(fn () => Invoice::generateInvoiceCode()),
                        Forms\Components\Select::make('registration_entry_id')
                            ->label('Đăng ký trực tiếp')
                            ->relationship('registrationEntry', 'name')
                            ->searchable(['name', 'bks'])
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - {$record->bks}")
                            ->required()
                            ->columnSpan(1),
                        // Forms\Components\TextInput::make('normalized_license_plate')
                        //     ->label('Biển số chuẩn hóa')
                        //     ->maxLength(255)
                        //     ->placeholder('Tự động chuẩn hóa từ đăng ký')
                        //     ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Thông tin thanh toán')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Số tiền')
                            ->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->prefix('₫')
                            ->minValue(0)
                            ->step(1000)
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('is_paid')
                            ->label('Đã thanh toán')
                            ->default(false)
                            ->inline(false)
                            ->live()
                            ->columnSpan(1),
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Thời gian thanh toán')
                            ->visible(fn (Get $get) => $get('is_paid'))
                            ->default(fn (Get $get) => $get('is_paid') ? now() : null)
                            ->columnSpan(1),
                        Forms\Components\Select::make('payment_method')
                            ->label('Phương thức thanh toán')
                            ->options([
                                'Trả tiền cho bảo vệ' => 'Trả tiền cho bảo vệ',
                                'Chuyển khoản' => 'Chuyển khoản',
                                'Tiền mặt' => 'Tiền mặt',
                                'Thẻ' => 'Thẻ',
                            ])
                            ->visible(fn (Get $get) => $get('is_paid'))
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Thông tin bổ sung')
                    ->schema([
                        Forms\Components\TextInput::make('file_path')
                            ->label('Đường dẫn file PDF')
                            ->maxLength(255)
                            ->placeholder('Tự động tạo khi xuất hóa đơn')
                            ->disabled(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Ghi chú')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
            ]);
    }
}
