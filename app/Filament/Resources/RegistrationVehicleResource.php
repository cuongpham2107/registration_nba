<?php

namespace App\Filament\Resources;

use App\Filament\Exports\RegistrationVehicleExporter;
use App\Filament\Resources\RegistrationVehicleResource\Actions\ApproveVehicleAction;
use App\Filament\Resources\RegistrationVehicleResource\Actions\CancelApproveVehicleAction;
use App\Filament\Resources\RegistrationVehicleResource\Filters\RegistrationVehicleFilter;
use App\Filament\Resources\RegistrationVehicleResource\Pages;
use App\Models\RegistrationVehicle;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use App\Services\HawbService;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\TextInput;


class RegistrationVehicleResource extends Resource
{
    protected static ?string $model = RegistrationVehicle::class;

    protected static ?string $modelLabel = 'Đăng ký xe khai thác';

    protected static ?string $navigationLabel = 'Đăng ký xe khai thác';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form

            ->schema([
                Grid::make([
                    'default' => 1,
                    'md' => 2,
                ])->schema([
                    Forms\Components\TextInput::make('driver_name')
                        ->label('Tên tài xế')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('driver_phone')
                        ->label('Số điện thoại')
                        ->required()
                        ->maxLength(20),

                    Forms\Components\TextInput::make('driver_id_card')
                        ->label('Số CMND/CCCD')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('vehicle_number')
                        ->label('Biển số xe')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('name')
                        ->label('Tên đơn vị')
                        ->options(HawbService::getListAgentApi())
                        ->multiple()
                        ->afterStateHydrated(function ($component, $state) {
                            // Convert comma-separated string back to array for editing
                            if (is_string($state) && !empty($state)) {
                                $array = array_map('trim', explode(',', $state));
                                $component->state($array);
                            }
                        })
                        ->dehydrateStateUsing(function ($state) {
                            // Convert array to comma-separated string before saving
                            if (is_array($state)) {
                                return implode(', ', $state);
                            }
                            return $state;
                        }),

                    Forms\Components\Toggle::make('is_priority')
                        ->label('Ưu tiên')
                        ->helperText('Đánh dấu nếu đăng ký xe khai thác này là ưu tiên')
                        ->onIcon('heroicon-o-arrow-up')
                        ->offIcon('heroicon-o-arrow-down')
                        ->inline(false),
                    // Forms\Components\TextInput::make('hawb_number')
                    //     ->label('Số HAWB')
                    //     ->disabled(),
                    // Forms\Components\TextInput::make('pcs')
                    //     ->label('Số kiện')
                    //     ->disabled(),
                        // ->helperText('Danh sách số HAWB đã thêm sẽ hiển thị bên dưới'),
                    Forms\Components\Hidden::make('hawb_number')
                        ->dehydrated(true),
                    TableRepeater::make('hawbs')
                            ->label('Danh sách HAWB')
                            ->columns(1)
                            ->headers([
                                Header::make('hawb_number')->label('Số HAWB'),
                                Header::make('pcs')->label('Số PCS')->width('80px')->align(Alignment::Center),
                            ])
                            ->schema([
                                TextInput::make('hawb_number')
                                    ->label('Số HAWB')
                                    ->required()
                                    ->maxLength(255)
                                    ->reactive(),
                                TextInput::make('pcs')
                                    ->label('Số PCS')
                                    ->maxLength(255)
                            ])
                            ->afterStateHydrated(function (TableRepeater $component, $state, $record) {
                                // Get data from the hidden hawb_number field instead
                                if ($record && $record->hawb_number) {
                                    $hawbData = $record->hawb_number;
                                    if (is_string($hawbData)) {
                                        try {
                                            $decoded = json_decode($hawbData, true, 512, JSON_THROW_ON_ERROR);
                                            if (is_array($decoded)) {
                                                $component->state($decoded);
                                                return;
                                            }
                                        } catch (\JsonException $e) {
                                            // If JSON decode fails, set empty array
                                        }
                                    }
                                }
                                $component->state([]);
                            })
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Update the hidden hawb_number field when TableRepeater changes
                                if (is_array($state)) {
                                    $filtered = array_filter($state, function($item) {
                                        return is_array($item) && !empty($item['hawb_number']);
                                    });
                                    $set('hawb_number', !empty($filtered) ? json_encode(array_values($filtered)) : null);
                                }
                            })
                            ->dehydrated(false) // Don't save this field directly
                            ->addActionLabel('Thêm số HAWB')
                            ->addable(false)
                            ->reorderable(false)
                            ->emptyLabel('Chưa có HAWB nào được thêm')
                            ->minItems(0)
                            ->columnSpanFull(),
                    Forms\Components\DateTimePicker::make('expected_in_at')
                        ->label('Thời gian vào dự kiến')
                        ->seconds(false)
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('notes')
                        ->label('Ghi chú')
                        ->columnSpanFull(),
                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->header(view('filament.resources.tables.header'))
            ->description(function () {
                $user = auth()->user();
                if (! $user || ! $user->hasRole('approve_vehicle')) {
                    return '';
                }

                $count = RegistrationVehicle::where('status', 'sent')
                    ->count();

                if ($count > 0) {
                    // Tạo URL filter cho các bản ghi chưa duyệt (status=sent)
                    $baseUrl = route('filament.admin.resources.registration-vehicles.index');
                    $filterUrl = $baseUrl.'?tableFilters[vehicle_filter][status]=sent';

                    return new \Illuminate\Support\HtmlString(
                        '<a href="'.$filterUrl.'" style="display: flex; align-items: center; gap: 6px; padding: 8px 10px; background: linear-gradient(135deg, #ff6b6b 0%, #ffa5a5 100%); color: white; border-radius: 8px; font-weight: 500; font-size: 0.875rem; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform=\'translateY(-1px)\'; this.style.boxShadow=\'0 4px 12px rgba(255, 107, 107, 0.4)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'none\';">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px; flex-shrink: 0;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            </svg>
                            <span>Bạn có <strong style="font-size: 1em; padding: 0 2px;">'.$count.'</strong> yêu cầu đăng ký xe đang chờ phê duyệt - <u>Nhấn để xem</u></span>
                        </a>'
                    );
                }

                return '';
            })
            ->emptyStateHeading('Không có đăng ký xe khai thác nào')
            ->emptyStateIcon('heroicon-o-truck')
            ->emptyStateDescription('Hiện tại chưa có đăng ký xe khai thác nào được tạo. Vui lòng nhấn nút "Thêm đăng ký xe" để tạo mới.')
            ->columns([

                Tables\Columns\TextColumn::make('driver_name')
                    ->label('Tên tài xế')
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string =>
                        mb_convert_case($state, MB_CASE_TITLE, "UTF-8")
                    ),

                Tables\Columns\TextColumn::make('driver_phone')
                    ->label('Số điện thoại')
                    ->searchable(),
                Tables\Columns\TextColumn::make('driver_id_card')
                    ->label('Số CMND/CCCD')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle_number')
                    ->label('Biển số xe')
                    ->formatStateUsing(fn (string $state): string => strtoupper(str_replace(' ', '', $state)))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên đơn vị')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hawb_number')
                    ->label('Số Hawb')
                    ->formatStateUsing(function (?string $state): \Illuminate\Support\HtmlString {
                        if (empty($state)) {
                            return new \Illuminate\Support\HtmlString('');
                        }
                        
                        // If it's already a plain string (not JSON), return as is but uppercase
                        if (!str_starts_with(trim($state), '[') && !str_starts_with(trim($state), '{')) {
                            return new \Illuminate\Support\HtmlString('<div>' . strtoupper($state) . '</div>');
                        }
                        
                        // Try to decode JSON
                        $decoded = json_decode($state, true);
                        
                        // If JSON decode failed or result is not an array, return original value but uppercase
                        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                            return new \Illuminate\Support\HtmlString('<div>' . strtoupper($state) . '</div>');
                        }
                        
                        // Format the HAWB list - each item on a new line
                        $hawbs = [];
                        foreach ($decoded as $item) {
                            // Ensure $item is an array and has required fields
                            if (is_array($item) && isset($item['hawb_number']) && !empty($item['hawb_number'])) {
                                $hawb = strtoupper($item['hawb_number']);
                                if (isset($item['pcs']) && !empty($item['pcs'])) {
                                    $hawb .= ' <span style="color: #6b7280; font-size: 0.875em;">(' . $item['pcs'] . ' PCS)</span>';
                                }
                                $hawbs[] = '<div style="padding: 2px 0;">' . $hawb . '</div>';
                            }
                        }
                        
                        if (!empty($hawbs)) {
                            return new \Illuminate\Support\HtmlString(implode('', $hawbs));
                        }
                        
                        return new \Illuminate\Support\HtmlString('<div>' . strtoupper($state) . '</div>');
                    })
                    ->html()    
                    ->copyable()
                    ->copyMessage('Đã sao chép số HAWB vào clipboard')
                    ->copyMessageDuration(1500)
                    ->searchable(),
                Tables\Columns\TextColumn::make('pcs')
                    ->label('Số kiện')
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('expected_in_at')
                    ->label('Thời gian vào dự kiến')
                    ->dateTime('d/m/Y H:i')
                    ->searchable()
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'none' => 'Chưa gửi',
                        'sent' => 'Cần duyệt',
                        'approve' => 'Đã phê duyệt',
                        'entering' => 'Đang vào',
                        'exited' => 'Đã ra',
                        'reject' => 'Từ chối',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'none' => 'gray',
                        'sent' => 'warning',
                        'approve' => 'success',
                        'entering' => 'primary',
                        'exited' => 'info',
                        'reject' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_priority')
                    ->label('Ưu tiên')
                    ->sortable()
                    ->onIcon('heroicon-o-arrow-up')
                    ->offIcon('heroicon-o-arrow-down')
                    ->beforeStateUpdated(function ($record, $state) {
                        if($record->status === 'approve' && $record->registerDirectly !== null) {
                            $record->registerDirectly->is_priority = $state;
                            $record->registerDirectly->save();
                        }
                    }),
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Người phê duyệt')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Thời gian phê duyệt')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ngày cập nhật')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort', 'asc')
            ->filters([
                RegistrationVehicleFilter::make(),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
            ->modifyQueryUsing(function ($query) {
                $user = auth()->user();

                // Nếu chưa login, return query rỗng
                if (! $user) {
                    return $query->whereRaw('1 = 0');
                }

                // Super admin và approve_vehicle thấy tất cả
                if ($user->hasRole('super_admin') || $user->hasRole('approve_vehicle')) {
                    // Luôn ưu tiên sort column, sau đó mới đến các sort khác
                    return $query->orderBy('sort', 'asc');
                }

                // User thường (panel_user, approver) không thấy gì
                return $query->whereRaw('1 = 0');

            })// phân quyền đến từng dòng dữ liệu
            ->actions([
                ApproveVehicleAction::make(),
                Tables\Actions\ActionGroup::make([
                    CancelApproveVehicleAction::make(),
                    Tables\Actions\EditAction::make()
                        ->label('Sửa'),
                    Tables\Actions\DeleteAction::make(),
                ])->icon('heroicon-m-adjustments-vertical')
                    ->size(ActionSize::Small)
                    ->iconButton()
                    ->color('gray'),
            ], position: \Filament\Tables\Enums\ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ExportBulkAction::make()
                    ->label('Xuất Excel')
                    ->modalHeading('Xuất Excel đăng ký xe khai thác')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('success')
                    ->fileName(fn (Export $export): string => "Danh sách đăng ký xe khai thác-{$export->getKey()}.csv")
                    ->exporter(RegistrationVehicleExporter::class),
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
            'index' => Pages\ListRegistrationVehicles::route('/'),
            // 'create' => Pages\CreateRegistrationVehicle::route('/create'),
            // 'edit' => Pages\EditRegistrationVehicle::route('/{record}/edit'),
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
