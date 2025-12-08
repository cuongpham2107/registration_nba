<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationVehicleResource\Pages;
use App\Filament\Resources\RegistrationVehicleResource\Filters\RegistrationVehicleFilter;
use App\Filament\Resources\RegistrationVehicleResource\Actions\ApproveVehicleAction;
use App\Filament\Resources\RegistrationVehicleResource\Actions\CancelApproveVehicleAction;
use App\Models\Area;
use App\Models\RegistrationVehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Enums\FiltersLayout;
use App\Filament\Exports\RegistrationVehicleExporter;
use Filament\Actions\Exports\Models\Export;

class RegistrationVehicleResource extends Resource
{
    protected static ?string $model = RegistrationVehicle::class;
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
                            Forms\Components\TextInput::make('name')
                                ->label('Tên đơn vị')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('hawb_number')
                                ->label('Số Hawb')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('pcs')
                                ->label('Số kiện')
                                ->maxLength(255),
                            Forms\Components\Toggle::make('is_priority')
                                ->label('Ưu tiên')
                                ->helperText('Đánh dấu nếu đăng ký xe khai thác này là ưu tiên')
                                ->onIcon('heroicon-o-arrow-up')
                                ->offIcon('heroicon-o-arrow-down')
                                ->inline(false),
                            Forms\Components\DateTimePicker::make('expected_in_at')
                                ->label('Thời gian vào dự kiến')
                                ->seconds(false)
                                ->required()
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('notes')
                                ->label('Ghi chú')
                                ->columnSpanFull(),
                        ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->description(function () {
                $user = auth()->user();
                if (!$user || !$user->hasRole('approve_vehicle')) {
                    return '';
                }

                $count = RegistrationVehicle::where('status', 'sent')
                    ->count();

                if ($count > 0) {
                    // Tạo URL filter cho các bản ghi chưa duyệt (status=sent)
                    $baseUrl = route('filament.admin.resources.registration-vehicles.index');
                    $filterUrl = $baseUrl . '?tableFilters[vehicle_filter][status]=sent';

                    return new \Illuminate\Support\HtmlString(
                        '<a href="' . $filterUrl . '" style="display: flex; align-items: center; gap: 6px; padding: 8px 10px; background: linear-gradient(135deg, #ff6b6b 0%, #ffa5a5 100%); color: white; border-radius: 8px; font-weight: 500; font-size: 0.875rem; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform=\'translateY(-1px)\'; this.style.boxShadow=\'0 4px 12px rgba(255, 107, 107, 0.4)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'none\';">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px; flex-shrink: 0;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            </svg>
                            <span>Bạn có <strong style="font-size: 1em; padding: 0 2px;">' . $count . '</strong> yêu cầu đăng ký xe đang chờ phê duyệt - <u>Nhấn để xem</u></span>
                        </a>'
                    );
                }

                return '';
            })
            ->columns([
                Tables\Columns\TextColumn::make('sort')
                    ->label('Thứ tự')
                    ->sortable()
                    ->alignment(Alignment::Center),
                Tables\Columns\TextColumn::make('driver_name')
                    ->label('Tên tài xế')
                    ->searchable(),

                Tables\Columns\TextColumn::make('driver_phone')
                    ->label('Số điện thoại')
                    ->searchable(),
                Tables\Columns\TextColumn::make('driver_id_card')
                    ->label('Số CMND/CCCD')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle_number')
                    ->label('Biển số xe')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên đơn vị')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hawb_number')
                    ->label('Số Hawb')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pcs')
                    ->label('Số kiện')
                    ->searchable(),
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
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'none' => 'Chưa gửi',
                        'sent' => 'Cần duyệt',
                        'approve' => 'Đã phê duyệt',
                        'reject' => 'Từ chối',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'none' => 'gray',
                        'sent' => 'warning',
                        'approve' => 'success',
                        'reject' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_priority')
                    ->label('Ưu tiên')
                    ->sortable()
                    ->onIcon('heroicon-o-arrow-up')
                    ->offIcon('heroicon-o-arrow-down'),
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
                if (!$user) {
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
                    ->fileName(fn(Export $export): string => "Danh sách đăng ký xe khai thác-{$export->getKey()}.csv")
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
