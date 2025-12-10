<?php

namespace App\Filament\Resources;

use App\Filament\Exports\RegisterDirectlyExporter;
use App\Filament\Resources\RegisterDirectlyResource\Actions\GiveCardAction;
use App\Filament\Resources\RegisterDirectlyResource\Actions\ReturnCardAction;
use App\Filament\Resources\RegisterDirectlyResource\Filters\ListFilterRegisterDirectly;
use App\Filament\Resources\RegisterDirectlyResource\Pages;
use App\Models\Area;
use App\Models\RegisterDirectly;
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
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
                            ->prefixIcon('heroicon-o-map-pin'),
                        Forms\Components\TextInput::make('bks')
                            ->label('Biển kiểm soát')
                            ->prefixIcon('heroicon-o-truck')
                            ->required(),
                        Forms\Components\TextInput::make('contact_person')
                            ->label('Người liên hệ'),
                        Forms\Components\Toggle::make('is_priority')
                            ->label('Ưu tiên')
                            ->helperText('Đánh dấu nếu đây là đơn đăng ký ưu tiên')
                            ->onIcon('heroicon-s-arrow-up')
                            ->offIcon('heroicon-s-arrow-down')
                            ->inline(false),
                        Forms\Components\Textarea::make('job')
                            ->label('Mục đích công việc')
                            ->rows(5),
                    ])->columnSpan(1),
                Section::make('Thông tin thẻ')
                    ->schema([
                        Forms\Components\Select::make('card_id')
                            ->label('Thẻ')
                            // ->required()
                            ->columnSpanFull()
                            ->relationship(
                                name: 'card',
                                titleAttribute: 'card_name',
                                modifyQueryUsing: fn (Builder $query) => $query->where('status', 'inactive')
                            )
                            ->searchable(['card_name', 'card_number'])
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('account_id')
                                    ->label('Mã tài khoản')
                                    ->required(),
                                Forms\Components\TextInput::make('card_number')
                                    ->label('Số thẻ')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('card_name')
                                    ->label('Tên thẻ')
                                    ->required(),
                                Forms\Components\Select::make('status')
                                    ->label('Trạng thái')
                                    ->options([
                                        'active' => 'Đang sử dụng',
                                        'inactive' => 'Chưa sử dụng',
                                        'blocked' => 'Bị khóa',
                                    ])
                                    ->default('inactive')
                                    ->required(),
                            ]),
                        Forms\Components\Select::make('areas')
                            ->label('Khu vực')
                            ->multiple()
                            ->options(Area::all()->pluck('name', 'code'))
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('start_date')
                            ->displayFormat('d/m/Y h:i')
                            ->seconds(false)
                            ->label('Giờ vào')
                            ->required(),
                        Forms\Components\DateTimePicker::make('end_date')
                            ->displayFormat('d/m/Y h:i')
                            ->seconds(false)
                            ->label('Giờ ra dự kiến')
                            ->rules([
                                fn (Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                    if ($record['status'] != 'sent') {
                                        if (Carbon::parse($value, 'Asia/Ho_Chi_Minh')->isBefore(Carbon::parse($get('start_date'), 'Asia/Ho_Chi_Minh'))) {
                                            $fail('Ngày, giờ kết thúc phải lớn hơn ngày, giờ bắt đầu.');
                                        }
                                    }

                                },
                                fn (Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($record) {
                                    if ($record['status'] != 'sent') {
                                        if (Carbon::parse($value, 'Asia/Ho_Chi_Minh')->lessThanOrEqualTo(Carbon::now('Asia/Ho_Chi_Minh'))) {
                                            $fail('Ngày, giờ kết thúc phải lớn hơn ngày, giờ hiện tại.');
                                        }
                                    }

                                },
                            ]),
                        Forms\Components\DateTimePicker::make('actual_date_in')
                            ->label('Giờ vào thực tế')
                            ->displayFormat('d/m/Y H:i A')
                            ->prefixIcon('heroicon-s-calendar-days')
                            ->seconds(false)
                            ->readonly(),
                        Forms\Components\DateTimePicker::make('actual_date_out')
                            ->label('Giờ ra thực tế')
                            ->displayFormat('d/m/Y H:i A')
                            ->prefixIcon('heroicon-s-calendar-days')
                            ->seconds(false)
                            ->readonly(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'coming_in' => 'Đang vào',
                                'came_out' => 'Đã ra',
                            ])
                            ->default('coming_in')
                            ->label('Trạng thái')
                            // ->required()
                            ->columnSpanFull(),
                    ])->columnSpan(1)->columns(2),
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
                    ->label('Loại'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Họ và tên')
                    ->formatStateUsing(
                        fn (RegisterDirectly $record): string => isset(explode('|', $record->name)[0]) ? trim(explode('|', $record->name)[0]) : $record->name
                    )
                    ->description(function (RegisterDirectly $record): string {
                        $parts = explode('|', $record->name);

                        return isset($parts[1]) ? trim($parts[1]) : '';
                    })
                    ->weight(FontWeight::Bold),
                Tables\Columns\TextColumn::make('papers')
                    ->label('Số CCCD'),
                Tables\Columns\TextColumn::make('bks')
                    ->label('Biển kiểm soát'),

                Tables\Columns\TextColumn::make('areas')
                    ->label('Khu vực')
                    ->formatStateUsing(function (string $state): string {
                        $area = Area::where('code', $state)->first();

                        return $area ? $area->name : '';
                    })
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
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
                    }),

                // Tables\Columns\ColumnGroup::make('Thời gian dự kiến', [
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Giờ vào dự kiến')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->alignment(Alignment::Center),
                //     Tables\Columns\TextColumn::make('end_date')
                //         ->label('Giờ ra dự kiến')
                //         ->dateTime('d/m/Y H:i')
                //         ->sortable()
                //         ->alignment(Alignment::Center),
                // ])->alignment(Alignment::Center)->wrapHeader(),
                Tables\Columns\ColumnGroup::make('Thời gian thực tế', [
                    Tables\Columns\TextColumn::make('actual_date_in')
                        ->label('Giờ vào thực tế')
                        ->dateTime('d/m/Y H:i'),
                    Tables\Columns\TextColumn::make('actual_date_out')
                        ->label('Giờ ra thực tế')
                        ->dateTime('d/m/Y H:i')
                        ->alignment(Alignment::Center),
                ])->alignment(Alignment::Center)->wrapHeader(),
                Tables\Columns\TextColumn::make('job')
                    ->label('Mục đích')
                    ->formatStateUsing(
                        fn (RegisterDirectly $record): string => isset(explode('|', $record->job)[0]) ? trim(explode('|', $record->job)[0]) : $record->job
                    )
                    ->description(function (RegisterDirectly $record): string {
                        $parts = explode('|', $record->job);

                        return isset($parts[1]) ? trim($parts[1]) : '';
                    })
                    ->toggleable(),

                Tables\Columns\ToggleColumn::make('is_priority')
                    ->label('Ưu tiên')
                    ->onIcon('heroicon-s-arrow-up')
                    ->offIcon('heroicon-s-arrow-down')
                    ->disabled(),
                Tables\Columns\TextColumn::make('card.card_name')
                    ->label('Thẻ')
                    ->numeric(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->toggleable(),
            ])
            ->defaultSort('sort', 'asc')
            ->modifyQueryUsing(function (Builder $query) {
                // Lấy filter data từ request
                $tableFilters = request()->input('tableFilters', []);
                $isPriorityEnabled = $tableFilters['date_range']['is_priority'] ?? false;

                // Nếu filter is_priority được bật, sắp xếp theo is_priority trước
                if ($isPriorityEnabled === true) {
                    return $query->orderByRaw('is_priority DESC, sort ASC, created_at DESC');
                }

                return $query;
            })
            ->filters([
                ListFilterRegisterDirectly::make(),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
            ->deferLoading()
            ->defaultPaginationPageOption(25)
            ->actions([
                GiveCardAction::make(),
                ReturnCardAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->slideOver()
                        ->modalWidth(MaxWidth::SixExtraLarge)
                        ->hidden(fn ($record) => $record->status === 'came_out'),
                    Tables\Actions\DeleteAction::make(),
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
            //
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
}
