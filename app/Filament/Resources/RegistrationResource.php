<?php

namespace App\Filament\Resources;

use App\Models\Customer;
use App\Models\Registration;
use App\Models\Area;
use App\Filament\Resources\RegistrationResource\Pages;
use App\Filament\Resources\RegistrationResource\Actions\SendMailAction;
use App\Filament\Resources\RegistrationResource\Actions\ApproveRegistrationAction;
use App\Filament\Resources\RegistrationResource\Actions\RefuseRegistrationAction;
use App\Filament\Resources\RegistrationResource\Actions\ImportCustomersAction;
use App\Filament\Exports\RegistrationExporter;
use App\Filament\Resources\RegistrationResource\Filters\RegistrationFilter;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Get;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Throwable;
use Closure;
use Carbon\Carbon;
use Filament\Tables\Enums\FiltersLayout;



class RegistrationResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Registration::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationLabel = 'Đăng ký khách';
     protected static ?string $modelLabel = 'Đăng ký khách';
     
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 1;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        self::formWizardStepRegistration(),
                        self::formWizardStepInfoCustomer(),
                    ])->columnSpanFull(),
            ]);
    }
    protected static function formWizardStepRegistration()
    {
        return Forms\Components\Tabs\Tab::make('Đăng ký khách')
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Đơn vị khách')
                    ->required()
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 3,
                    ]),
                Forms\Components\TextInput::make('bks')
                    ->prefixIcon('heroicon-o-truck')
                    ->label('BKS ô tô')
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 3,
                    ]),
                Forms\Components\Textarea::make('purpose')
                    ->label('Mục đích')
                    ->required()
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 6,
                    ]),
                Forms\Components\DateTimePicker::make('start_date')
                    ->displayFormat('d/m/Y h:i')
                    ->locale('vi')
                    ->seconds(false)
                    ->label('Giờ vào dự kiến')
                    ->required()
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 3,
                    ]),
                Forms\Components\DateTimePicker::make('end_date')
                    ->displayFormat('d/m/Y h:i')
                    ->locale('vi')
                    ->seconds(false)
                    ->label('Giờ ra dự kiến')
                    ->required()
                    ->rules([
                        fn(Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                            if (($record['status'] ?? null) != 'sent') {
                                if (Carbon::parse($value, 'Asia/Ho_Chi_Minh')->isBefore(Carbon::parse($get('start_date'), 'Asia/Ho_Chi_Minh'))) {
                                    $fail('Ngày, giờ kết thúc phải lớn hơn ngày, giờ bắt đầu.');
                                }
                            }

                        },
                        fn(Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                            if (($record['status'] ?? null) != 'sent') {
                                if (Carbon::parse($value, 'Asia/Ho_Chi_Minh')->lessThanOrEqualTo(Carbon::now('Asia/Ho_Chi_Minh'))) {
                                    $fail('Ngày, giờ kết thúc phải lớn hơn ngày, giờ hiện tại.');
                                }
                            }

                        }
                    ])
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 3,
                    ]),
                // Forms\Components\Select::make('approver_id')
                //     ->label('Người phê duyệt')
                //     ->relationship('approver', 'name', function ($query) {
                //         $query->whereHas('roles', function ($q) {
                //             $q->where('name', 'approver');
                //         });
                //     })
                //     ->default(auth()->id())
                //     ->hidden()
                //     ->dehydrated()
                //     ->searchable()
                //     ->preload()
                //     ->required()
                //     ->columnSpan([
                //         'sm' => 1,
                //         'md' => 2,
                //         'lg' => 2,
                //     ]),
                Forms\Components\Textarea::make('asset')
                    ->label('Tài sản')
                    ->rows(2)
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 3,
                    ]),
                Forms\Components\Textarea::make('note')
                    ->label('Ghi chú')
                    ->rows(2)
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 3,
                    ]),
            ])->columns([
                'sm' => 1,
                'md' => 2,
                'lg' => 6,
            ]);
    }
    protected static function formWizardStepInfoCustomer()
    {
        return Forms\Components\Tabs\Tab::make('Thông tin khách')
            ->schema([
                TableRepeater::make('customers')
                    ->relationship()
                    ->label('')
                    ->headers([
                        Header::make('Tên khách')->width(200),
                        Header::make('Số giấy tờ')->width(150),
                        Header::make('Loại giấy tờ')->width(150),
                        Header::make('Khu vực')->width(200),
                        Header::make('Biển số')->width(150),
                        Header::make('Ghi chú')->width(250),
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên khách')
                            ->required(),
                        Forms\Components\TextInput::make('papers')
                            ->label('Số giấy tờ')
                            ->required(),
                        Forms\Components\TextInput::make('type')
                            ->label('Loại giấy tờ')
                            ->required(),
                        Forms\Components\Select::make('areas')
                            ->label('Khu vực')
                            ->multiple()
                            ->options(Area::all()->pluck('name', 'code'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('license_plate')
                            ->label('Biển số'),
                        Forms\Components\TextInput::make('note')
                            ->label('Ghi chú'),
                    ])
                    ->defaultItems(1)
                    ->columns(6)
                    ->extraActions([
                        ImportCustomersAction::make()
                    ])
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->header(view('filament.resources.tables.header'))
            ->description(function () {
                $user = auth()->user();
                if (!$user || !$user->hasRole('approver')) {
                    return '';
                }
                $count = Registration::where('approver_id', $user->id)
                    ->where('status', 'sent')
                    ->whereNull('type')
                    ->count();
                    
                if ($count > 0) {
                    // Tạo URL filter cho các bản ghi chưa duyệt (status=sent, type=null)
                    $filterUrl = route('filament.admin.resources.registrations.index', [
                        'tableFilters' => [
                            'date_range' => [
                                'status' => 'sent',
                                'type' => 'none', 
                            ]
                        ]
                    ]);
                    
                    return new \Illuminate\Support\HtmlString(
                        '<a href="' . $filterUrl . '" style="display: flex; align-items: center; gap: 6px; padding: 8px 10px; background: linear-gradient(135deg, #ff6b6b 0%, #ffa5a5 100%); color: white; border-radius: 8px; font-weight: 500; font-size: 0.875rem; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform=\'translateY(-1px)\'; this.style.boxShadow=\'0 4px 12px rgba(255, 107, 107, 0.4)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'none\';">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px; flex-shrink: 0;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                            <span>Bạn có <strong style="font-size: 1em; padding: 0 2px;">' . $count . '</strong> yêu cầu đăng ký đang chờ phê duyệt </span>
                        </a>'
                    );
                }
                return '';
            })
            ->emptyStateHeading('Không có đơn đăng ký khách nào')
            ->emptyStateIcon('heroicon-o-user')
            ->emptyStateDescription('Hiện tại chưa có đơn đăng ký khách nào được tạo. Vui lòng nhấn nút "Đăng ký khách mới" để tạo mới.')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label("Mã ĐK")
                    ->width('1%')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Đơn vị khách')
                    ->weight(FontWeight::Bold)
                    ->width('15%')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('purpose')
                    ->label('Mục đích')
                    ->width('15%')
                    ->limit(25)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('bks')
                    ->label('BKS ô tô')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\ColumnGroup::make('Thời gian dự kiến', [
                    Tables\Columns\TextColumn::make('start_date')
                        ->label('Giờ vào')
                        ->dateTime('d/m/Y H:i')
                        ->searchable()
                        ->alignment(Alignment::Center)
                        ->sortable()
                        ->toggleable(),
                    Tables\Columns\TextColumn::make('end_date')
                        ->label('Giờ ra')
                        ->dateTime('d/m/Y H:i')
                        ->searchable()
                        ->alignment(Alignment::Center)
                        ->sortable()
                        ->toggleable(),
                ])->alignment(Alignment::Center)->wrapHeader(),

                Tables\Columns\TextColumn::make('status')
                    ->Label('Trạng thái')
                    ->weight(FontWeight::Bold)
                    ->badge()
                    ->toggleable()
                    ->color(fn(string $state): string => match ($state) {
                        'sent' => 'success',
                        'not_yet_sent' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'sent' => 'Đã gửi',
                        'not_yet_sent' => 'Chưa gửi',
                    }),
                Tables\Columns\TextColumn::make('type')
                    ->Label('Duyệt')
                    ->weight(FontWeight::ExtraBold)
                    ->badge()
                    ->toggleable()
                    ->color(fn(string $state): string => match ($state) {
                        'browse' => 'success',
                        'refuse' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'browse' => 'Duyệt',
                        'refuse' => 'Từ chối',
                    }),
                Tables\Columns\TextColumn::make('type_date')
                    ->Label('Ngày duyệt')
                    ->dateTime('d/m/Y')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('approver.name')
                    ->Label('Người duyệt')
                    ->searchable()
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->Label('Người tạo')
                    ->badge()
                    ->separator(',')
                    ->limit(15)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->Label('Ngày tạo')
                    ->date('d/m/Y')
                    ->limit(20)
                    ->toggleable(),
            ])
            ->recordClasses(fn (Model $record) => match ($record->status) {
                'sent' => 'border-s-2 border-orange-600 dark:border-orange-300',
                default => '',
            })
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25)
            ->modifyQueryUsing(function ($query) {
                $user = auth()->user();
                // Nếu chưa login, return query rỗng
                if (!$user) {
                    return $query->whereRaw('1 = 0');
                }
                // Super admin thấy tất cả
                if ($user->hasRole('super_admin')) {
                    return $query;
                }
                // Approver chỉ thấy các bản ghi mà mình là người phê duyệt
                if ($user->hasRole('approver')) {
                    return $query->where('approver_id', $user->id);
                }
                // User thường chỉ thấy của mình (người tạo)
                return $query->where('user_id', $user->id);
            })// phân quyền đến từng dòng dữ liệu
            ->filters([
                RegistrationFilter::make(),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
            ->actions([
                SendMailAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->modalWidth(MaxWidth::SixExtraLarge)
                        ->hidden(fn(Registration $record) => $record->status === 'sent' && $record->type === 'browse' || $record->type === 'refuse' || $record->user_id !== auth()->id()),
                    Tables\Actions\ViewAction::make()->modalWidth(MaxWidth::SixExtraLarge),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn(Registration $record) => $record->status === 'sent' && $record->type === 'browse' || $record->type === 'refuse' || $record->user_id !== auth()->id()),
                    ApproveRegistrationAction::make(),
                    RefuseRegistrationAction::make(),
                ])->icon('heroicon-m-adjustments-vertical')
                    ->size(ActionSize::Small)
                    ->iconButton()
                    ->color('gray'),

            ], position: \Filament\Tables\Enums\ActionsPosition::BeforeColumns)
            ->bulkActions([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->label('Xuất Excel')
                        ->modalHeading('Xuất Excel đăng ký khách')
                        ->icon('heroicon-o-inbox-arrow-down')
                        ->color('success')
                        ->fileName(fn (Export $export): string => "Danh sách đăng ký khách-{$export->getKey()}.csv")
                        ->exporter(RegistrationExporter::class),
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
            'index' => Pages\ListRegistrations::route('/'),
            // 'create' => Pages\CreateRegistration::route('/create'),
            // 'edit' => Pages\EditRegistration::route('/{record}/edit'),
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

