<?php

namespace App\Filament\Resources;

use App\Filament\Exports\RegisterDirectlyExporter;
use App\Filament\Resources\RegisterDirectlyResource\Actions\GiveCardAction;
use App\Filament\Resources\RegisterDirectlyResource\Actions\ReturnCardAction;
use App\Filament\Resources\RegisterDirectlyResource\Filters\ListFilterRegisterDirectly;
use App\Filament\Resources\RegisterDirectlyResource\Pages;
use App\Models\Area;
use App\Models\RegisterDirectly;
use App\Models\RegistrationVehicle;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Closure;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class RegisterDirectlyResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = RegisterDirectly::class;

    protected static ?string $modelLabel = 'Ra vào';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Danh sách ra vào';

    protected static ?string $title = 'Danh sách ra vào';

    protected ?string $heading = 'Danh sách ra vào';

    protected static ?int $navigationSort = 1;

    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Thông tin cơ bản')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Họ và tên')
                            ->required(),
                        Forms\Components\TextInput::make('papers')
                            ->label('Số CCCD')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('address')
                            ->label('Địa chỉ')
                            ->hidden(fn (?Model $record) => $record?->type === 'vehicle')
                            ->prefixIcon('heroicon-o-map-pin'),
                        Forms\Components\TextInput::make('bks')
                            ->label('Biển kiểm soát')
                            ->prefixIcon('heroicon-o-truck')
                            ->formatStateUsing(fn (?string $state) => $state ? strtoupper(str_replace(' ', '', $state)) : '')
                            ->required(),
                        Forms\Components\Select::make('fee_id')
                            ->label('Loại xe / Mức phí')
                            ->relationship(
                                name: 'fee',
                                titleAttribute: 'ticket_code',
                            )
                            ->hidden(fn (?Model $record) => $record?->type !== 'vehicle')
                            ->searchable(['vehicle_type', 'ticket_code'])
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('contact_person')
                            ->hidden(fn (?Model $record) => $record?->type === 'vehicle')
                            ->label('Người liên hệ'),
                        Forms\Components\Toggle::make('is_priority')
                            ->label('Ưu tiên')
                            ->helperText('Đánh dấu nếu đây là đơn đăng ký ưu tiên')
                            ->onIcon('heroicon-s-arrow-up')
                            ->offIcon('heroicon-s-arrow-down')
                            ->hidden(fn (?Model $record) => $record?->type !== 'vehicle')
                            ->inline(false),
                        Forms\Components\Textarea::make('job')
                            ->label('Mục đích công việc')
                            ->rows(5),
                    ])->columnSpan(1),
                Forms\Components\Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Thông tin thẻ')
                            ->schema([
                                Forms\Components\Select::make('cards')
                                    ->label('Thẻ')
                                    ->multiple()
                                    ->columnSpanFull()
                                    ->relationship(
                                        name: 'cards',
                                        titleAttribute: 'card_name',
                                        modifyQueryUsing: function (Builder $query, Forms\Components\Component $component) {
                                            $record = $component->getRecord();
                                            $query->where(function ($q) use ($record) {
                                                $q->where('status', 'inactive')
                                                    ->where(function ($q) {
                                                        $q->whereNull('expiry_date')
                                                            ->orWhere('expiry_date', '>=', now());
                                                    });
                                                if ($record) {
                                                    $q->orWhereHas('registerDirectlies', fn ($q) => $q->where('register_directly_id', $record->id));
                                                }
                                            });
                                        }
                                    )
                                    ->searchable(['card_name', 'card_number'])
                                    ->preload(),
                                Forms\Components\Select::make('areas')
                                    ->label('Khu vực')
                                    ->multiple()
                                    ->options(Area::all()->pluck('name', 'code'))
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull(),
                                TableRepeater::make('registrationVehicle.customers')
                                    ->label('Danh sách phụ xe')
                                    ->headers([
                                        Header::make('name')->label('Tên phụ xe'),
                                        Header::make('papers')->label('Giấy tờ'),
                                    ])
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Tên phụ xe')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('papers')
                                            ->label('Giấy tờ')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->afterStateHydrated(function (TableRepeater $component, $state, $record) {
                                        if ($record && $record->registrationVehicle?->customers?->isNotEmpty()) {
                                            $component->state($record->registrationVehicle->customers->toArray());
                                        }
                                    })
                                    ->dehydrated(false)
                                    ->addActionLabel('Thêm phụ xe')
                                    ->reorderable(false)
                                    ->emptyLabel('Chưa có phụ xe nào')
                                    ->minItems(0)
                                    ->columnSpanFull(),
                                Forms\Components\DateTimePicker::make('start_date')
                                    ->displayFormat('d/m/Y h:i')
                                    ->seconds(false)
                                    ->label('Giờ vào')
                                    ->placeholder('Chọn ngày, giờ vào')
                                    ->native(true)
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->hidden(fn (?Model $record) => $record?->type === 'vehicle')
                                    ->required(),
                                Forms\Components\DateTimePicker::make('end_date')
                                    ->displayFormat('d/m/Y h:i')
                                    ->seconds(false)
                                    ->label('Giờ ra dự kiến')
                                    ->placeholder('Chọn ngày, giờ kết thúc dự kiến')
                                    ->native(true)
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->hidden(fn (?Model $record) => $record?->type === 'vehicle')
                                    ->rules([
                                        fn (Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                            if (($record['status'] ?? null) != 'sent') {
                                                if (Carbon::parse($value, 'Asia/Ho_Chi_Minh')->isBefore(Carbon::parse($get('start_date'), 'Asia/Ho_Chi_Minh'))) {
                                                    $fail('Ngày, giờ kết thúc phải lớn hơn ngày, giờ bắt đầu.');
                                                }
                                            }

                                        },
                                        fn (Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($record) {
                                            if (($record['status'] ?? null) != 'sent') {
                                                if (Carbon::parse($value, 'Asia/Ho_Chi_Minh')->lessThanOrEqualTo(Carbon::now('Asia/Ho_Chi_Minh'))) {
                                                    $fail('Ngày, giờ kết thúc phải lớn hơn ngày, giờ hiện tại.');
                                                }
                                            }

                                        },
                                    ]),
                                Forms\Components\DateTimePicker::make('actual_date_in')
                                    ->label('Giờ vào thực tế')
                                    ->displayFormat('d/m/Y H:i')
                                    ->prefixIcon('heroicon-s-calendar-days')
                                    ->seconds(false)
                                    ->readonly()
                                    ->placeholder('Chọn ngày, giờ vào thực tế')
                                    ->native(true),
                                Forms\Components\DateTimePicker::make('actual_date_out')
                                    ->label('Giờ ra thực tế')
                                    ->displayFormat('d/m/Y H:i')
                                    ->prefixIcon('heroicon-s-calendar-days')
                                    ->seconds(false)
                                    ->readonly()
                                    ->placeholder('Chọn ngày, giờ ra thực tế')
                                    ->native(true),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'coming_in' => 'Đang vào',
                                        'temporary_out' => 'Ra tạm thời',
                                        'came_out' => 'Đã ra',
                                    ])
                                    ->default('coming_in')
                                    ->label('Trạng thái')
                                    // ->readOnly()
                                    // ->required()
                                    ->hidden(fn (?Model $record) => $record?->type === 'vehicle')
                                    ->columnSpanFull(),
                            ])->columns(2),
                        Section::make('Thông tin phê duyệt')
                            ->schema([
                                Forms\Components\Placeholder::make('approved_by')
                                    ->label('Người phê duyệt')
                                    ->content(fn (?RegisterDirectly $record): string => self::getApproverName($record)),
                                Forms\Components\Placeholder::make('approved_at')
                                    ->label('Thời gian phê duyệt')
                                    ->content(fn (?RegisterDirectly $record): string => self::getApprovedAt($record)),
                            ])
                            ->hidden(fn (?RegisterDirectly $record): bool => ! self::hasApproval($record))
                            ->columns(2),
                    ]),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->header(view('filament.resources.tables.header'))
            ->emptyStateHeading('Không có khách hay xe khai thác nào')
            ->emptyStateDescription('Hiện tại chưa có khách hay xe khai thác nào.')
            ->columns([
                Tables\Columns\IconColumn::make('type')
                    ->icon(fn (?string $state): string => match ($state) {
                        'passenger' => 'heroicon-o-user',
                        'vehicle' => 'heroicon-o-truck',
                        default => 'heroicon-o-user',
                    })
                    ->sortable(
                        query: function (Builder $query, string $direction): Builder {
                            $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';

                            return $query->orderByRaw(
                                "CASE type
                                    WHEN 'passenger' THEN 1
                                    WHEN 'vehicle' THEN 2
                                    ELSE 3
                                END {$direction}"
                            );
                        }
                    )
                    ->label('Loại'),
                TextColumn::make('name')
                    ->label('Họ và tên')
                    ->formatStateUsing(
                        fn (RegisterDirectly $record): string => isset(explode('|', $record->name)[0]) ? trim(explode('|', mb_convert_case($record->name, MB_CASE_TITLE, 'UTF-8'))[0]) : mb_convert_case($record->name, MB_CASE_TITLE, 'UTF-8')
                    )
                    ->description(function (RegisterDirectly $record): string {
                        $parts = explode('|', $record->name);
                        $text = isset($parts[1]) ? trim($parts[1]) : '';

                        return mb_strimwidth($text, 0, 15, '...');
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
                    ->description(fn (?Model $record) => $record?->fee ? "{$record->fee->ticket_code}" : '')
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
                    ->sortable(
                        query: function (Builder $query, string $direction): Builder {
                            $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';

                            return $query->orderByRaw(
                                "CASE status
                                    WHEN 'coming_in' THEN 1
                                    WHEN 'temporary_out' THEN 2
                                    WHEN 'came_out' THEN 3
                                    ELSE 4
                                END {$direction}"
                            );
                        }
                    )
                    ->badge()
                    ->color(function ($state) {
                        if (is_null($state) || $state === 'none' || $state === '') {
                            return 'warning';
                        }

                        return match ($state) {
                            'coming_in' => 'danger',
                            'temporary_out' => 'warning',
                            'came_out' => 'primary',
                        };
                    })
                    ->formatStateUsing(function ($state) {
                        if (is_null($state) || $state === 'none' || $state === '') {
                            return 'Chờ vào';
                        }

                        return match ($state) {
                            'coming_in' => 'Đang vào',
                            'temporary_out' => 'Ra tạm thời',
                            'came_out' => 'Đã ra',
                        };
                    })
                    ->toggleable(),

                // Tables\Columns\ColumnGroup::make('Thời gian dự kiến', [
                TextColumn::make('start_date')
                    ->label('Giờ vào dự kiến')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->toggleable(),
                //     Tables\Columns\TextColumn::make('end_date')
                //         ->label('Giờ ra dự kiến')
                //         ->dateTime('d/m/Y H:i')
                //         ->sortable()
                //         ->alignment(Alignment::Center),
                // ])->alignment(Alignment::Center)->wrapHeader(),
                Tables\Columns\ColumnGroup::make('Thời gian thực tế', [
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
                        fn (RegisterDirectly $record): string => isset(explode('|', $record->job)[0]) ? trim(explode('|', $record->job)[0]) : $record->job
                    )
                    ->description(function (RegisterDirectly $record): string {
                        $parts = explode('|', $record->job);

                        return isset($parts[1]) ? trim($parts[1]) : '';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\ToggleColumn::make('is_priority')
                    ->label('Ưu tiên')
                    ->onIcon('heroicon-s-arrow-up')
                    ->offIcon('heroicon-s-arrow-down')
                    ->disabled(),
                TextColumn::make('cards.card_name')
                    ->label('Thẻ')
                    ->badge()
                    ->separator(',')
                    ->summarize([
                        Tables\Columns\Summarizers\Count::make()
                            ->label('Thẻ đã phát:'),
                    ]),

                TextColumn::make('invoice.amount')
                    ->label('Số tiền')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? number_format($state, 0, ',', '.') : '')
                    ->alignment(Alignment::Center)
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('VND')
                            ->label('Tổng tiền:'),
                    ]),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort', 'asc')
            ->filters([
                ListFilterRegisterDirectly::make(),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
            ->deferLoading()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->actions([

                GiveCardAction::make(),
                ReturnCardAction::make(),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->slideOver()
                        ->modalWidth(MaxWidth::SixExtraLarge),
                    Tables\Actions\EditAction::make()
                        ->slideOver()
                        ->modalWidth(MaxWidth::SixExtraLarge)
                        ->hidden(fn ($record) => $record->status === 'came_out'),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('view_invoice')
                        ->label('Hóa đơn')
                        ->icon('heroicon-m-printer')
                        ->color('success')
                        ->hidden(fn (RegisterDirectly $record) => $record->type !== 'vehicle' || ! $record->invoice)
                        ->action(function (RegisterDirectly $record, Component $livewire) {
                            $downloadUrl = route('invoice.download', ['registerDirectly' => $record->id]);
                            $livewire->js("window.printFile('{$downloadUrl}')");
                        }),
                    ActivityLogTimelineTableAction::make('Activities')
                        ->hidden(fn () => ! auth()->user()->hasRole('super_admin'))
                        ->label('Lịch sử')
                        ->icon('heroicon-m-clock')
                        ->color('info')
                        ->timelineIcons([
                            'created' => 'heroicon-m-check-badge',
                            'updated' => 'heroicon-m-pencil-square',
                            'deleted' => 'heroicon-m-trash',
                        ])
                        ->timelineIconColors([
                            'created' => 'success',
                            'updated' => 'warning',
                            'deleted' => 'danger',
                        ]),
                ])
                    ->icon('heroicon-m-adjustments-vertical')
                    ->size(ActionSize::Small)
                    ->iconButton()
                    ->color('gray')->link()->label(''),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                ExportBulkAction::make()
                    ->label('Xuất Excel')
                    ->modalHeading('Xuất Excel đăng ký ra vào')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('success')
                    ->fileName(fn (Export $export): string => "Danh sách khách ra vào-{$export->getKey()}.csv")
                    ->exporter(RegisterDirectlyExporter::class),
            ]);
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
            'index' => Pages\ListRegisterDirectlies::route('/'),
            // 'create' => Pages\CreateRegisterDirectly::route('/create'),
            // 'view' => Pages\ViewRegisterDirectly::route('/{record}'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    private static function resolveApprovalVehicle(?RegisterDirectly $record): ?RegistrationVehicle
    {
        if (! $record) {
            return null;
        }

        return $record->registrationVehicle
            ?? RegistrationVehicle::where('id_registration_directly', $record->id)->first();
    }

    public static function hasApproval(?RegisterDirectly $record): bool
    {
        if (! $record) {
            return false;
        }

        if ($record->registration?->type || $record->registration?->approver) {
            return true;
        }

        return (bool) self::resolveApprovalVehicle($record)?->approver;
    }

    public static function getApproverName(?RegisterDirectly $record): string
    {
        if (! $record) {
            return 'Chưa phê duyệt';
        }

        if ($record->registration?->approver) {
            return $record->registration->approver->name;
        }

        if ($record->registration?->type) {
            return $record->registration->type === 'browse' ? 'Đã duyệt' : 'Đã từ chối';
        }

        return self::resolveApprovalVehicle($record)?->approver?->name ?? 'Chưa phê duyệt';
    }

    public static function getApprovedAt(?RegisterDirectly $record): string
    {
        if (! $record) {
            return 'Chưa phê duyệt';
        }

        if ($record->registration?->type_date) {
            return Carbon::parse($record->registration->type_date)->format('d/m/Y H:i');
        }

        $vehicle = self::resolveApprovalVehicle($record);

        return $vehicle?->approved_at
            ? Carbon::parse($vehicle->approved_at)->format('d/m/Y H:i')
            : 'Chưa phê duyệt';
    }
}
