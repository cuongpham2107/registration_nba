<?php

namespace App\Filament\Resources;

use App\Filament\Exports\InvoiceExporter;
use App\Filament\Resources\InvoiceResource\Filters\InvoiceFilter;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Hóa đơn';

    protected static ?string $modelLabel = 'Hóa đơn';

    protected static ?string $pluralModelLabel = 'Hóa đơn';

    protected static ?int $navigationSort = 3;

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
                            ->default(fn () => Invoice::generateInvoiceCode())
                            ->columnSpan(2),

                        Forms\Components\Select::make('register_directly_id')
                            ->label('Đăng ký trực tiếp')
                            ->relationship('registerDirectly', 'name')
                            ->searchable(['name', 'bks'])
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - {$record->bks}")
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('normalized_license_plate')
                            ->label('Biển số chuẩn hóa')
                            ->maxLength(255)
                            ->placeholder('Tự động chuẩn hóa từ đăng ký')
                            ->columnSpan(2),

                        Forms\Components\Select::make('car_catalog_id')
                            ->label('Thông tin xe')
                            ->relationship('carCatalog', 'unit')
                            ->searchable(['name', 'license_plate'])
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - {$record->license_plate}")
                            ->placeholder('Chọn xe từ danh mục')
                            ->columnSpan(2),

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
                            ->inline(false)
                            ->live()
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Thời gian thanh toán')
                            ->placeholder('Chọn ngày, giờ thanh toán')
                            ->native(true)
                            ->prefixIcon('heroicon-o-calendar')
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

                        Forms\Components\Toggle::make('is_invoiced')
                            ->label('Đã xuất hóa đơn')
                            ->inline(false)
                            ->default(false)
                            ->live()
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('invoiced_at')
                            ->label('Thời gian xuất hóa đơn')
                            ->placeholder('Chọn ngày, giờ xuất hóa đơn')
                            ->native(true)
                            ->prefixIcon('heroicon-o-calendar')
                            ->visible(fn (Forms\Get $get) => $get('is_invoiced'))
                            ->default(fn (Forms\Get $get) => $get('is_invoiced') ? now() : null)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('file_path')
                            ->label('Đường dẫn file PDF')
                            ->maxLength(255)
                            ->placeholder('Tự động tạo khi xuất hóa đơn')
                            ->columnSpanFull()
                            ->suffixAction(
                                Action::make('download_invoice_form')
                                    ->icon('heroicon-o-inbox-arrow-down')
                                    ->color('success')
                                    ->url(fn ($record) => $record->file_path ? asset('storage/'.$record->file_path) : null)
                                    ->openUrlInNewTab()
                                    ->visible(fn ($record) => $record->file_path && Storage::disk('public')->exists($record->file_path))
                            ),

                        Forms\Components\Textarea::make('notes')
                            ->label('Ghi chú')
                            ->rows(3)
                            ->columnSpan(4),
                    ])
                    ->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('STT')
                    ->sortable(false)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('registerDirectly.id')
                    ->label('Số vé')
                    ->formatStateUsing(fn ($state) => "T1" . "-" . str_pad($state ?? 0, 2, '0', STR_PAD_LEFT))
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('registerDirectly.actual_date_in')
                    ->label('Ngày vào')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->alignCenter()
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('registerDirectly.actual_date_out')
                    ->label('Ngày ra')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->alignCenter()
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('invoice_code')
                    ->label('Mã hoá đơn')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('registerDirectly.bks')
                    ->label('Biển kiểm soát')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->alignCenter()
                    ->color('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label('Loại xe/ Trọng tải')
                    ->state(function ($record) {
                        $fee = $record->registerDirectly?->fee;
                        if (!$fee) return '-';
                        return $fee->ticket_code;
                    })
                    ->toggleable(),

                Tables\Columns\ColumnGroup::make('Thời gian', [
                    Tables\Columns\TextColumn::make('entry_time')
                        ->label('Giờ vào')
                        ->getStateUsing(function ($record) {
                            return $record->registerDirectly?->actual_date_in
                                ? Carbon::parse($record->registerDirectly->actual_date_in)->format('H:i')
                                : null;
                        })
                        ->alignCenter()
                        ->placeholder('-')
                        ->toggleable(),
                    Tables\Columns\TextColumn::make('exit_time')
                        ->label('Giờ ra')
                        ->getStateUsing(function ($record) {
                            return $record->registerDirectly?->actual_date_out
                                ? Carbon::parse($record->registerDirectly->actual_date_out)->format('H:i')
                                : null;
                        })
                        ->alignCenter()
                        ->placeholder('-')
                        ->toggleable(),
                ])->alignCenter(),

                Tables\Columns\TextColumn::make('duration')
                    ->label('Thời gian khai thác')
                    ->getStateUsing(function ($record) {
                        $rd = $record->registerDirectly;
                        if (!$rd) return null;

                        $start = $rd->actual_date_in;
                        $end = $rd->actual_date_out;

                        if (!$start || !$end) return null;

                        $start = Carbon::parse($start);
                        $end = Carbon::parse($end);

                        if ($end->lessThan($start)) return null;

                        $diff = $start->diff($end);
                        $hours = $diff->h + ($diff->d * 24);

                        return sprintf('%02d:%02d', $hours, $diff->i);
                    })
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Phí khai thác')
                    ->money('VND')
                    ->sortable()
                    ->alignRight()
                    ->weight('bold')
                    ->toggleable(),
            ])
            ->filters([
                InvoiceFilter::make(),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modal()
                    ->iconButton()
                    ->tooltip('Xem chi tiết')
                    ->modalHeading('Chỉnh sửa hóa đơn')
                    ->modalWidth('6xl')
                    ->modalDescription('Nhập thông tin hóa đơn cần chỉnh sửa'),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('mark_invoiced')
                        ->label('Đã xuất hóa đơn')
                        ->icon('heroicon-o-document-check')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Xác nhận xuất hóa đơn')
                        ->modalDescription('Bạn có chắc chắn muốn đánh dấu hóa đơn này đã được xuất?')
                        ->modalSubmitActionLabel('Xác nhận')
                        ->hidden(fn ($record) => $record->is_invoiced)
                        ->action(function ($record) {
                            $record->update([
                                'is_invoiced' => true,
                                'invoiced_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Đã xuất hóa đơn')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('confirm_payment')
                        ->label('Xác nhận đã thanh toán')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Xác nhận thanh toán')
                        ->modalDescription('Bạn có chắc chắn muốn đánh dấu hóa đơn này đã thanh toán?')
                        ->modalSubmitActionLabel('Xác nhận')
                        ->hidden(fn ($record) => $record->is_paid)
                        ->action(function ($record) {
                            $record->update([
                                'is_paid' => true,
                                'paid_at' => now(),
                                'payment_method' => 'Đơn vị trả tiền',
                            ]);

                            Notification::make()
                                ->title('Đã xác nhận thanh toán')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('download_pdf')
                        ->label('Tải hoá đơn')
                        ->icon('heroicon-o-inbox-arrow-down')
                        ->url(fn ($record) => $record->file_path ? asset('storage/'.$record->file_path) : null)
                        ->openUrlInNewTab()
                        ->visible(fn ($record) => $record->file_path && Storage::disk('public')->exists($record->file_path)),
                ])
                    ->icon('heroicon-m-adjustments-vertical')
                    ->size(ActionSize::Small)
                    ->iconButton()
                    ->color('gray'),
            ], position: ActionsPosition::BeforeColumns)
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

                            Notification::make()
                                ->title('Đã xác nhận thanh toán')
                                ->body('Đã cập nhật trạng thái thanh toán cho '.$records->count().' hóa đơn.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('mark_invoiced')
                        ->label('Đã xuất hóa đơn')
                        ->icon('heroicon-o-document-check')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Xác nhận xuất hóa đơn')
                        ->modalDescription('Bạn có chắc chắn muốn đánh dấu các hóa đơn đã chọn là đã xuất?')
                        ->modalSubmitActionLabel('Xác nhận')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'is_invoiced' => true,
                                    'invoiced_at' => now(),
                                ]);
                            });

                            Notification::make()
                                ->title('Đã xuất hóa đơn')
                                ->body('Đã cập nhật trạng thái xuất hóa đơn cho '.$records->count().' hóa đơn.')
                                ->success()
                                ->send();
                        }),

                    
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                Tables\Actions\ExportBulkAction::make()
                        ->label('Xuất Excel')
                        ->modalHeading('Xuất Excel hóa đơn')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->fileName(fn (Export $export): string => "Danh sách hóa đơn-{$export->getKey()}.xlsx")
                        ->exporter(InvoiceExporter::class),

            ])
            ->groups([
                Tables\Grouping\Group::make('carCatalog.unit.name')
                    ->label('Đơn vị')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession()
            ->deferLoading()
            ->extremePaginationLinks()
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->recordUrl(null)
            ->recordAction(null)
            ->searchOnBlur()
            ->searchDebounce('500ms');
    }

    public static function getRelations(): array
    {
        return [
            ActivitylogRelationManager::class,
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
