<?php

namespace App\Filament\Resources\RegistrationEntries\Tables;

use App\Filament\Exports\RegistrationEntryExporter;
use App\Filament\Resources\RegistrationEntries\Actions\GiveCardAction;
use App\Filament\Resources\RegistrationEntries\Actions\ReturnCardAction;
use App\Filament\Resources\RegistrationEntries\Filters\RegistrationEntryFilter;
use App\Models\Area;
use App\Models\RegistrationEntry;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\Exports\Models\Export;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Request;

class RegistrationEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->header(view('filament.resources.tables.header'))
            ->emptyStateHeading('Không có khách hay xe khai thác nào')
            ->emptyStateDescription('Hiện tại chưa có khách hay xe khai thác nào.')
            ->columns(self::getColumns())
            ->defaultSort('sort', 'asc')
            ->modifyQueryUsing(fn ($query) => self::modifyQuery($query))
            ->filters(self::getFilters(), layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
            ->deferLoading()
            ->defaultPaginationPageOption(25)
            ->recordActions(self::getRecordActions(), position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions(self::getToolbarActions());
    }

    private static function getColumns(): array
    {
        return [
            IconColumn::make('type')
                ->icon(fn (?string $state): string => match ($state) {
                    'passenger' => 'heroicon-o-user',
                    'vehicle' => 'heroicon-o-truck',
                    default => 'heroicon-o-user',
                })
                ->label('Loại'),
            TextColumn::make('name')
                ->label('Họ và tên')
                ->formatStateUsing(
                    fn (RegistrationEntry $record): string => isset(explode('|', $record->name)[0]) ? trim(explode('|', mb_convert_case($record->name, MB_CASE_TITLE, 'UTF-8'))[0]) : mb_convert_case($record->name, MB_CASE_TITLE, 'UTF-8')
                )
                ->description(function (RegistrationEntry $record): string {
                    $parts = explode('|', $record->name);

                    return isset($parts[1]) ? trim($parts[1]) : '';
                })
                ->weight(FontWeight::Bold)
                ->toggleable(),
            TextColumn::make('papers')
                ->label('Số CCCD')
                ->weight(FontWeight::Bold)
                ->toggleable(),
            TextColumn::make('bks')
                ->label('Biển kiểm soát')
                ->weight(FontWeight::Bold)
                ->formatStateUsing(fn (?string $state): string => $state ? strtoupper($state) : '')
                ->toggleable(),
            TextColumn::make('areas')
                ->label('Khu vực')
                ->formatStateUsing(function (string $state): string {
                    $area = Area::query()->where('code', $state)->first();

                    return $area ? $area->name : '';
                })
                ->badge()
                ->toggleable(),
            TextColumn::make('status')
                ->label('Trạng thái')
                ->sortable()
                ->badge()
                ->color(function ($state) {
                    if (is_null($state) || $state === 'none' || $state === '') {
                        return 'warning';
                    }

                    return match ($state) {
                        'coming_in' => 'danger',
                        'came_out' => 'primary',
                    };
                })
                ->formatStateUsing(function ($state) {
                    if (is_null($state) || $state === 'none' || $state === '') {
                        return 'Chờ vào';
                    }

                    return match ($state) {
                        'coming_in' => 'Đang vào',
                        'came_out' => 'Đã ra',
                    };
                })
                ->toggleable(),
            TextColumn::make('start_date')
                ->label('Giờ vào dự kiến')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->alignment(Alignment::Center)
                ->toggleable(),
            ColumnGroup::make('Thời gian thực tế', [
                TextColumn::make('actual_date_in')
                    ->label('Giờ vào thực tế')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(),
                TextColumn::make('actual_date_out')
                    ->label('Giờ ra thực tế')
                    ->dateTime('d/m/Y H:i')
                    ->alignment(Alignment::Center)
                    ->toggleable(),
            ])->alignment(Alignment::Center)->wrapHeader(),
            TextColumn::make('job')
                ->label('Mục đích')
                ->formatStateUsing(
                    fn (RegistrationEntry $record): string => isset(explode('|', $record->job)[0]) ? trim(explode('|', $record->job)[0]) : $record->job
                )
                ->description(function (RegistrationEntry $record): string {
                    $parts = explode('|', $record->job);

                    return isset($parts[1]) ? trim($parts[1]) : '';
                })
                ->toggleable(isToggledHiddenByDefault: true),
            ToggleColumn::make('is_priority')
                ->label('Ưu tiên')
                ->onIcon('heroicon-s-arrow-up')
                ->offIcon('heroicon-s-arrow-down')
                ->disabled(),
            TextColumn::make('card.card_name')
                ->label('Thẻ')
                ->numeric(),
            TextColumn::make('created_at')
                ->label('Ngày tạo')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->alignment(Alignment::Center)
                ->toggleable(),
        ];
    }

    private static function modifyQuery($query)
    {
        $tableFilters = Request::input('tableFilters', []);
        $isPriorityEnabled = $tableFilters['date_range']['is_priority'] ?? false;

        if ($isPriorityEnabled === true) {
            return $query->orderByRaw('is_priority DESC, sort ASC, created_at DESC');
        }

        return $query;
    }

    private static function getFilters(): array
    {
        return [
            RegistrationEntryFilter::make(),
        ];
    }

    private static function getRecordActions(): array
    {
        return [
            GiveCardAction::make(),
            ReturnCardAction::make(),
            ActionGroup::make([
                EditAction::make()
                    ->slideOver()
                    ->modalWidth(Width::SixExtraLarge)
                    ->hidden(fn ($record) => $record->status === 'came_out'),
                DeleteAction::make(),
            ])
                ->icon('heroicon-m-adjustments-vertical')
                ->size(Size::Small)
                ->iconButton()
                ->color('gray')->link()->label(''),
        ];
    }

    private static function getToolbarActions(): array
    {
        return [
            DeleteBulkAction::make(),
            ExportBulkAction::make()
                ->label('Xuất Excel')
                ->modalHeading('Xuất Excel đăng ký ra vào')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('success')
                ->fileName(fn (Export $export): string => "Danh sách khách ra vào-{$export->getKey()}.csv")
                ->exporter(RegistrationEntryExporter::class),
        ];
    }
}
