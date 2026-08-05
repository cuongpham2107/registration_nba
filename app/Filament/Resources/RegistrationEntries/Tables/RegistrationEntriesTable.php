<?php

namespace App\Filament\Resources\RegistrationEntries\Tables;

use App\Filament\Exports\RegistrationEntryExporter;
use App\Filament\Resources\RegistrationEntries\Actions\GiveCardAction;
use App\Filament\Resources\RegistrationEntries\Actions\ReEnterCardAction;
use App\Filament\Resources\RegistrationEntries\Actions\ReturnCardAction;
use App\Filament\Resources\RegistrationEntries\Filters\RegistrationEntryFilter;
use App\Filament\Resources\RegistrationEntries\Schemas\RegistrationEntryForm;
use App\Models\Area;
use App\Models\RegistrationEntry;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class RegistrationEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // ->header(view('filament.resources.tables.header'))
            ->emptyStateHeading('Không có khách hay xe khai thác nào')
            ->emptyStateDescription('Hiện tại chưa có khách hay xe khai thác nào.')
            ->columns(self::getColumns())
            ->modifyQueryUsing(fn ($query) => self::modifyQuery($query))
            ->filters(self::getFilters(), layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(1)
            ->deferLoading()
            ->deferFilters(false)
            ->defaultPaginationPageOption(25)
            ->recordActions(self::getRecordActions(), position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions(self::getToolbarActions());
    }

    /**
     * Reuse the resource table columns in other places (e.g. custom Pages).
     */
    public static function columns(): array
    {
        return self::getColumns();
    }

    /**
     * Columns preset for Pages: hide columns that are toggled hidden by default
     * (those are typically “optional” columns in the resource table).
     */
    public static function columnsForPage(): array
    {
        return self::getColumns(isToggledHiddenByDefault: false);
    }

    public static function filters(): array
    {
        return self::getFilters();
    }

    public static function recordActions(): array
    {
        return self::getRecordActions();
    }

    public static function toolbarActions(): array
    {
        return self::getToolbarActions();
    }

    private static function modifyQuery($query): Builder
    {
        $query
            ->where('type', 'inspection')
            ->getQuery()->orders = null;

        return $query->orderByRaw("
            CASE
                WHEN status = 'coming_in' THEN 0
                WHEN status = 'came_out' THEN 1
                WHEN status = 'none' OR status IS NULL OR status = '' THEN 2
                ELSE 3
            END ASC,
            created_at DESC
        ");
    }

    private static function getColumns(bool $isToggledHiddenByDefault = true): array
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
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: $isToggledHiddenByDefault),
            TextColumn::make('papers')
                ->label('Số CCCD')
                ->weight(FontWeight::Bold)
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: $isToggledHiddenByDefault),
            TextColumn::make('license_plate')
                ->label('Biển kiểm soát')
                ->weight(FontWeight::Bold)
                ->formatStateUsing(fn (?string $state): string => $state ? strtoupper($state) : '')
                ->searchable()
                ->limit(30)
                ->toggleable(),
            TextColumn::make('areas')
                ->label('Khu vực')
                ->formatStateUsing(function (string $state): string {
                    $area = Area::query()->where('code', $state)->first();

                    return $area ? $area->name : '';
                })
                ->badge()
                ->toggleable(isToggledHiddenByDefault: $isToggledHiddenByDefault),
            TextColumn::make('status')
                ->label('Trạng thái')
                ->alignment(Alignment::Center)
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
                ->toggleable(isToggledHiddenByDefault: $isToggledHiddenByDefault),
            ColumnGroup::make('Thời gian thực tế', [
                TextColumn::make('actual_date_in')
                    ->label('Giờ vào thực tế')
                    ->dateTime('d/m/Y H:i')
                    ->alignment(Alignment::Center)
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
                ->formatStateUsing(function (RegistrationEntry $record): string {
                    $parts = explode('|', $record->job);
                    $text = isset($parts[0]) ? trim($parts[0]) : $record->job;

                    return Str::limit($text, 30, '...');
                })
                ->description(function (RegistrationEntry $record): string {
                    $parts = explode('|', $record->job);
                    $text = isset($parts[1]) ? trim($parts[1]) : '';

                    return Str::limit($text, 50, '...');
                })
                ->toggleable(isToggledHiddenByDefault: $isToggledHiddenByDefault),
            TextColumn::make('card.card_name')
                ->label('Thẻ')
                // ->bagde()
                ->badge()
                ->numeric(),
            TextColumn::make('invoice.amount')
                ->label('Số tiền')
                ->money('VND')
                ->toggleable(isToggledHiddenByDefault: ! $isToggledHiddenByDefault),
            TextColumn::make('invoice.file_path')
                ->label('Hoá đơn')
                ->alignment(Alignment::Center)
                ->icon(fn (?string $state): ?string => $state ? 'heroicon-m-printer' : null)
                ->formatStateUsing(fn (?string $state): string => $state ? 'In vé' : '')
                ->color('info')
                ->width('150px')
                ->url(fn (?string $state): ?string => $state ? "javascript:printFile('".asset('storage/'.$state)."')" : null)
                ->toggleable(isToggledHiddenByDefault: ! $isToggledHiddenByDefault),
            TextColumn::make('created_at')
                ->label('Ngày tạo')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->alignment(Alignment::Center)
                ->toggleable(isToggledHiddenByDefault: true),
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
            ReEnterCardAction::make(),
            ActionGroup::make([
                ViewAction::make()
                    ->slideOver()
                    ->modalWidth(Width::SixExtraLarge)
                    ->schema(fn (Schema $schema): Schema => RegistrationEntryForm::configure($schema)),
                EditAction::make()
                    ->slideOver()
                    ->modalWidth(Width::SixExtraLarge)
                    ->schema(fn (Schema $schema): Schema => RegistrationEntryForm::configure($schema))
                    ->hidden(fn () => ! auth()->user()?->hasRole('super_admin')),
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
