<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::getColumns())
            ->filters(self::getFilters())
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
            TextColumn::make('payment_method')
                ->label('Phương thức thanh toán')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    'Trả tiền cho bảo vệ' => 'success',
                    default => 'gray'
                }),
            TextColumn::make('registrationEntry.vehicleRegistration.company')
                ->label('Có xuất hóa đơn không?')
                ->badge()
                ->formatStateUsing(fn ($state) => $state ? 'Có' : 'Không')
                ->alignCenter(),
            TextColumn::make('payment_method')
                ->label('Phương thức thanh toán')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    'Trả tiền cho bảo vệ' => 'success',
                    default => 'gray'
                }),
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
                }),
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
            TernaryFilter::make('is_paid')
                ->label('Trạng thái thanh toán')
                ->placeholder('Tất cả')
                ->trueLabel('Đã thanh toán')
                ->falseLabel('Chưa thanh toán'),
            SelectFilter::make('payment_method')
                ->label('Phương thức thanh toán')
                ->options([
                    'Trả tiền cho bảo vệ' => 'Trả tiền cho bảo vệ',
                ]),
            Filter::make('created_at')
                ->form([
                    DatePicker::make('created_from')
                        ->label('Từ ngày'),
                    DatePicker::make('created_until')
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
                }),
        ];
    }

    private static function getRecordActions(): array
    {
        return [
            Action::make('download_pdf')
                ->label('Xem HĐ')
                ->button()
                ->icon('heroicon-o-eye')
                ->url(fn ($record) => $record->file_path ? Storage::url($record->file_path) : null)
                ->openUrlInNewTab()
                ->visible(fn ($record) => $record->file_path && Storage::disk('public')->exists($record->file_path)),
            EditAction::make()
                ->label('Sửa')
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
