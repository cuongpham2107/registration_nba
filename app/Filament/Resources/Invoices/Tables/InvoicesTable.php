<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Filament\Resources\Invoices\Filters\InvoiceFilter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
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
            ->recordActions(self::getRecordActions())
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
                ->icon('heroicon-o-building-office-2')
                ->visible(fn ($record) => $record->company)
                ->fillForm(fn ($record) => $record->company->toArray())
                ->schema([
                    TextInput::make('tax_code')
                        ->label('Mã số thuế')
                        ->disabled(),
                    TextInput::make('name')
                        ->label('Tên công ty')
                        ->disabled(),
                    TextInput::make('phone')
                        ->label('Số điện thoại')
                        ->disabled(),
                    TextInput::make('address')
                        ->label('Địa chỉ')
                        ->disabled(),

                ])
                ->action(function ($record) {
                    $record->update([
                        'is_issued' => true,
                    ]);
                    Notification::make()
                        ->title('Đã xuất hóa đơn')
                        ->body('Đã xuất hóa đơn cho công ty '.$record->company->name)
                        ->success()
                        ->send();
                })
                ->modalSubmitActionLabel('Xác nhận xuất hóa đơn!'),
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
