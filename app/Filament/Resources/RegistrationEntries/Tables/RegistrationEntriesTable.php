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
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RegistrationEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->header(view('filament.resources.tables.header'))
            ->emptyStateHeading('Không có khách hay xe khai thác nào')
            ->emptyStateDescription('Hiện tại chưa có khách hay xe khai thác nào.')
            ->columns(self::getColumns())
            ->defaultSort('created_at', 'asc')
            ->modifyQueryUsing(fn ($query) => self::modifyQuery($query))
            ->filters(self::getFilters(), layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
            ->deferLoading()
            ->deferFilters(false)
            // ->groups([
            //     Group::make('license_plate')
            //         ->titlePrefixedWithLabel(false)
            //         ->getKeyFromRecordUsing(fn ($record): string => (int) $record->plate_count > 1
            //                 ? $record->license_plate
            //                 : '__single__'.$record->id
            //         )
            //         ->getTitleFromRecordUsing(fn ($record): ?string => (int) $record->plate_count > 1
            //                 ? $record->license_plate
            //                 : null
            //         ),
            // ])
            // ->defaultGroup('license_plate')
            ->defaultPaginationPageOption(25)
            ->recordActions(self::getRecordActions(), position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions(self::getToolbarActions());
    }

    private static function modifyQuery($query): Builder
    {
        // $query->addSelect([
        //     'registration_entries.*',
        //     DB::raw('(
        //             SELECT COUNT(*)
        //             FROM registration_entries re2
        //             WHERE re2.license_plate = registration_entries.license_plate
        //         ) as plate_count'),
        // ]);

        return $query;
    }

    private static function getColumns(): array
    {
        return [
            IconColumn::make('type')
                ->icon(fn (?string $state): string => match ($state) {
                    'working' => 'heroicon-o-user',
                    'inspection' => 'heroicon-o-truck',
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
            TextColumn::make('license_plate')
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
                        'entering' => 'danger',
                        'exited' => 'primary',
                    };
                })
                ->formatStateUsing(function ($state) {
                    if (is_null($state) || $state === 'none' || $state === '') {
                        return 'Chờ vào';
                    }

                    return match ($state) {
                        'entering' => 'Đang vào',
                        'exited' => 'Đã ra',
                    };
                })
                ->toggleable(),
            TextColumn::make('start_date')
                ->label('Giờ vào dự kiến')
                ->dateTime('d/m/Y H:i')
                ->icon('heroicon-s-calendar-days')
                ->sortable()
                ->alignment(Alignment::Center)
                ->toggleable(),
            ColumnGroup::make('Thời gian thực tế', [
                TextColumn::make('actual_date_in')
                    ->label('Giờ vào thực tế')
                    ->dateTime('d/m/Y H:i')
                    ->icon('heroicon-s-calendar-days')
                    ->toggleable(),
                TextColumn::make('actual_date_out')
                    ->label('Giờ ra thực tế')
                    ->dateTime('d/m/Y H:i')
                    ->icon('heroicon-s-calendar-days')
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
                    ->hidden(fn ($record) => $record->status === 'exited'),
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
