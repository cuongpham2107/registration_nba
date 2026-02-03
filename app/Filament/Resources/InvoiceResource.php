<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Hóa đơn';
    
    protected static ?string $modelLabel = 'Hóa đơn';
    
    protected static ?string $pluralModelLabel = 'Hóa đơn';
    
    protected static ?string $navigationGroup = 'Quản lý thanh toán';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin hóa đơn')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_code')
                            ->label('Mã hóa đơn')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Tự động tạo nếu để trống')
                            ->default(fn () => Invoice::generateInvoiceCode()),
                            
                        Forms\Components\Select::make('register_directly_id')
                            ->label('Đăng ký trực tiếp')
                            ->relationship('registerDirectly', 'name')
                            ->searchable(['name', 'bks'])
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - {$record->bks}")
                            ->required()
                            ->columnSpan(1),
                            
                        Forms\Components\TextInput::make('normalized_license_plate')
                            ->label('Biển số chuẩn hóa')
                            ->maxLength(255)
                            ->placeholder('Tự động chuẩn hóa từ đăng ký')
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Thông tin xe và công ty')
                    ->schema([
                        Forms\Components\Select::make('car_catalog_id')
                            ->label('Thông tin xe')
                            ->relationship('carCatalog', 'unit')
                            ->searchable(['name', 'license_plate'])
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - {$record->license_plate}")
                            ->placeholder('Chọn xe từ danh mục'),
                    ]),
                    
                Forms\Components\Section::make('Thông tin thanh toán')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Số tiền')
                            ->required()
                            ->numeric()
                            ->default(0.00)
                            ->prefix('₫')
                            ->minValue(0)
                            ->step(1000)
                            ->columnSpan(1),
                            
                        Forms\Components\Toggle::make('is_paid')
                            ->label('Đã thanh toán')
                            ->default(false)
                            ->live()
                            ->columnSpan(1),
                            
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Thời gian thanh toán')
                            ->visible(fn (Forms\Get $get) => $get('is_paid'))
                            ->default(fn (Forms\Get $get) => $get('is_paid') ? now() : null)
                            ->columnSpan(1),
                            
                        Forms\Components\Select::make('payment_method')
                            ->label('Phương thức thanh toán')
                            ->options([
                                'Trả tiền cho bảo vệ' => 'Trả tiền cho bảo vệ',
                                'Chuyển khoản' => 'Chuyển khoản',
                                'Tiền mặt' => 'Tiền mặt',
                                'Thẻ' => 'Thẻ',
                            ])
                            ->visible(fn (Forms\Get $get) => $get('is_paid'))
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Thông tin bổ sung')
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
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_code')
                    ->label('Mã hóa đơn')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                    
                // Tables\Columns\TextColumn::make('registerDirectly.bks')
                //     ->label('Biển số xe')
                //     ->searchable()
                //     ->sortable(),
                    
                Tables\Columns\TextColumn::make('registerDirectly.name')
                    ->label('Tên khách hàng')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('normalized_license_plate')
                    ->label('Biển số xe')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('carCatalog.unit.name')
                    ->label('Đơn vị')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->carCatalog?->company_name;
                    }),
                    
                Tables\Columns\TextColumn::make('amount')
                    ->label('Số tiền')
                    ->money('VND')
                    ->sortable()
                    ->alignRight()
                    ->weight('bold'),
                    
                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Đã thanh toán')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Phương thức thanh toán')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'Trả tiền cho bảo vệ' => 'success',
                        default => 'gray'
                    }),
                    
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Thời gian thanh toán')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Chưa thanh toán'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_paid')
                    ->label('Trạng thái thanh toán')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đã thanh toán')
                    ->falseLabel('Chưa thanh toán'),
                    
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Phương thức thanh toán')
                    ->options([
                        'Trả tiền cho bảo vệ' => 'Trả tiền cho bảo vệ',
                    ]),
                    
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Từ ngày'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Đến ngày'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('download_pdf')
                    ->label('Xem hóa đơn')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => $record->file_path ? \Storage::url($record->file_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->file_path && \Storage::disk('public')->exists($record->file_path)),
                    
                Tables\Actions\EditAction::make()
                    ->modal()
                    ->modalHeading('Chỉnh sửa hóa đơn')
                    ->modalDescription('Nhập thông tin hóa đơn cần chỉnh sửa'),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('confirm_payment')
                        ->label('Xác nhận đã thanh toán')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Xác nhận thanh toán')
                        ->modalDescription('Bạn có chắc chắn muốn đánh dấu các hóa đơn đã chọn là đã thanh toán?')
                        ->modalSubmitActionLabel('Xác nhận')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'is_paid' => true,
                                    'paid_at' => now(),
                                    'payment_method' => 'Đơn vị trả tiền',
                                ]);
                            });
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Đã xác nhận thanh toán')
                                ->body('Đã cập nhật trạng thái thanh toán cho ' . $records->count() . ' hóa đơn.')
                                ->success()
                                ->send();
                        }),
                        
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('carCatalog.unit.name')
                    ->label('Đơn vị')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            // 'create' => Pages\CreateInvoice::route('/create'),
            // 'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
