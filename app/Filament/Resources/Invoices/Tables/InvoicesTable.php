<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Filament\Resources\Invoices\Filters\InvoiceFilter;
use App\Models\Company;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::getColumns())
            ->filters(self::getFilters(), layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(5)
            ->deferFilters(false)
            ->recordActions(self::getRecordActions(), position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions(self::getBulkActions())
            ->groups(self::getGroups())
            ->defaultSort('created_at', 'desc');
    }

    private static function getColumns(): array
    {
        return [
            TextColumn::make('invoice_code')
                ->label('Mã hóa đơn')
                ->searchable()
                ->sortable()
                ->copyable(),
            TextColumn::make('registrationEntry.name')
                ->label('Tên khách hàng')
                ->searchable()
                ->sortable(),
            TextColumn::make('normalized_license_plate')
                ->label('Biển số xe')
                ->searchable()
                ->badge()
                ->color('gray'),
            TextColumn::make('amount')
                ->label('Số tiền')
                ->money('VND')
                ->sortable()
                ->alignEnd()
                ->weight('bold'),
            IconColumn::make('is_paid')
                ->label('Đã thanh toán')
                ->boolean()
                ->alignCenter()
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->trueColor('success')
                ->falseColor('danger'),
            TextColumn::make('company_id')
                ->label('Có xuất hóa đơn không?')
                ->badge()
                ->formatStateUsing(fn ($state) => $state ? 'Có' : 'Không')
                ->color(fn ($state) => $state ? 'success' : 'danger')
                ->alignCenter(),
            TextColumn::make('is_issued')
                ->label('Đã xuất hóa đơn?')
                ->badge()
                ->formatStateUsing(fn ($state) => $state ? 'Đã xuất' : 'Chưa xuất')
                ->color(fn ($state) => $state ? 'success' : 'warning')
                ->alignCenter(),
            IconColumn::make('is_paid')
                ->label('Đã thanh toán')
                ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                ->alignCenter(),
            TextColumn::make('payment_method')
                ->label('Phương thức thanh toán')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    'Trả tiền cho bảo vệ' => 'success',
                    default => 'gray'
                })
                ->alignCenter(),
            TextColumn::make('paid_at')
                ->label('Thời gian thanh toán')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->placeholder('Chưa thanh toán'),
            TextColumn::make('created_at')
                ->label('Ngày tạo')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(),
        ];
    }

    private static function getFilters(): array
    {
        return [
            InvoiceFilter::make(),
        ];
    }

    private static function getRecordActions(): array
    {
        return [
            Action::make('view_company')
                ->label('')
                ->button()
                ->tooltip('Xem thông tin xuất hoá đơn')
                ->color('success')
                ->modalHeading('Thông tin công ty xuất hóa đơn')
                ->modalDescription('Cơ quan/Tổ chức cần xuất hóa đơn tài chính')
                ->modalWidth(Width::Large)
                ->icon('heroicon-o-building-office-2')
                // ->visible(fn ($record) => $record->company)
                ->fillForm(fn ($record) => $record->company?->toArray() ?? [])
                ->schema([
                    TextInput::make('tax_code')
                        ->label('Mã số thuế')
                        ->live(onBlur: true)
                        ->aboveErrorMessage([
                            Icon::make(Heroicon::ArrowPath)
                                ->extraAttributes([
                                    'wire:loading' => true,
                                    'class' => 'animate-spin inline-block mr-1 h-4 w-4',
                                ]),
                            'Đang lấy thông tin công ty...',
                        ])
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (empty($state)) {
                                return;
                            }

                            $taxCode = preg_replace('/\D+/', '', (string) $state);

                            if (strlen($taxCode) < 10) {
                                return;
                            }

                            try {
                                $response = Http::timeout(8)
                                    ->withHeaders([
                                        'Accept' => '*/*',
                                    ])
                                    ->get("https://api.vietqr.io/v2/business/{$taxCode}");

                                $json = $response->json();
                                $data = $json['data'] ?? null;

                                if (is_array($data)) {
                                    $set('name', $data['name'] ?? null);
                                    $set('address', $data['address'] ?? null);
                                }
                            } catch (\Throwable $e) {
                                // Silent fail
                            }
                        })
                        ->disabled(fn ($record) => $record->is_issued),
                    TextInput::make('name')
                        ->label('Tên công ty')
                        ->disabled(fn ($record) => $record->is_issued),
                    TextInput::make('phone')
                        ->label('Số điện thoại')
                        ->disabled(fn ($record) => $record->is_issued),
                    TextInput::make('address')
                        ->label('Địa chỉ')
                        ->disabled(fn ($record) => $record->is_issued),

                ])
                ->action(function ($record, array $data) {
                    if ($record->is_issued) {
                        return;
                    }

                    if ($record->company) {
                        $record->company->update($data);
                    } else {
                        $company = null;
                        if (! empty($data['tax_code'])) {
                            $company = Company::updateOrCreate(
                                ['tax_code' => $data['tax_code']],
                                $data
                            );
                        } elseif (! empty($data['name'])) {
                            $company = Company::create($data);
                        }

                        if ($company) {
                            $record->update(['company_id' => $company->id]);
                        }
                    }

                    $record->update([
                        'is_issued' => true,
                    ]);

                    $record->refresh();

                    Notification::make()
                        ->title('Đã xuất hóa đơn')
                        ->body('Đã xuất hóa đơn cho công ty '.($record->company?->name ?? $data['name'] ?? ''))
                        ->success()
                        ->send();
                })
                ->modalSubmitActionLabel(fn ($record) => $record->is_issued ? 'Đã xuất hóa đơn' : 'Xác nhận xuất hóa đơn!'),
            Action::make('download_pdf')
                ->label('')
                ->button()
                ->tooltip('Xem vé')
                ->icon('heroicon-o-document-text')
                ->url(fn ($record) => $record->file_path ? Storage::url($record->file_path) : null)
                ->openUrlInNewTab()
                ->visible(fn ($record) => $record->file_path && Storage::disk('public')->exists($record->file_path)),
            EditAction::make()
                ->label('')
                ->button()
                ->modalHeading('Chỉnh sửa hóa đơn')
                ->modalDescription('Nhập thông tin hóa đơn cần chỉnh sửa'),
        ];
    }

    private static function getBulkActions(): array
    {
        return [
            BulkAction::make('confirm_payment')
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
            DeleteBulkAction::make(),
        ];
    }

    private static function getGroups(): array
    {
        return [
            Group::make('carCatalog.unit.name')
                ->label('Đơn vị')
                ->collapsible()
                ->titlePrefixedWithLabel(false),
        ];
    }
}
