<?php

namespace App\Filament\Resources\VehicleRegistrations\Tables;

use App\Filament\Exports\VehicleRegistrationExporter;
use App\Filament\Resources\VehicleRegistrations\Actions\ApproveVehicleRegistrationAction;
use App\Filament\Resources\VehicleRegistrations\Actions\CancelApproveVehicleRegistrationAction;
use App\Filament\Resources\VehicleRegistrations\Filters\VehicleRegistrationFilter;
use App\Models\VehicleRegistration;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\Exports\Models\Export;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class VehicleRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->header(view('filament.resources.tables.header'))
            ->description(self::getDescription())
            ->emptyStateHeading('Không có Đăng ký xe kiểm hoá nào')
            ->emptyStateIcon('heroicon-o-truck')
            ->emptyStateDescription('Hiện tại chưa có Đăng ký xe kiểm hoá nào được tạo. Vui lòng nhấn nút "Thêm đăng ký xe" để tạo mới.')
            ->columns(self::getColumns())
            ->defaultSort('sort', 'asc')
            ->deferFilters(false)
            ->filters(self::getFilters(), layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
            ->modifyQueryUsing(fn ($query) => self::modifyQuery($query))
            ->recordActions(self::getRecordActions(), position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions(self::getToolbarActions());
    }

    private static function getDescription(): string
    {
        $user = Auth::user();
        if (! $user || ! $user->hasRole('approve_vehicle')) {
            return '';
        }

        $count = VehicleRegistration::where('status', 'sent')->count();

        if ($count > 0) {
            $baseUrl = route('filament.admin.resources.vehicle-registrations.index');
            $filterUrl = $baseUrl.'?tableFilters[vehicle_filter][status]=sent';

            return '<a href="'.$filterUrl.'" style="display: flex; align-items: center; gap: 6px; padding: 8px 10px; background: linear-gradient(135deg, #ff6b6b 0%, #ffa5a5 100%); color: white; border-radius: 8px; font-weight: 500; font-size: 0.875rem; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform=\'translateY(-1px)\'; this.style.boxShadow=\'0 4px 12px rgba(255, 107, 107, 0.4)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'none\';">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px; flex-shrink: 0;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
                <span>Bạn có <strong style="font-size: 1em; padding: 0 2px;">'.$count.'</strong> yêu cầu đăng ký xe đang chờ phê duyệt - <u>Nhấn để xem</u></span>
            </a>';
        }

        return '';
    }

    private static function getColumns(): array
    {
        return [
            TextColumn::make('driver_name')
                ->label('Tên tài xế')
                ->searchable()
                ->formatStateUsing(fn (string $state): string => mb_convert_case($state, MB_CASE_TITLE, 'UTF-8')),
            TextColumn::make('driver_phone')
                ->label('Số điện thoại')
                ->searchable(),
            TextColumn::make('driver_id_card')
                ->label('Số CMND/CCCD')
                ->searchable(),
            TextColumn::make('vehicle_number')
                ->label('Biển số xe')
                ->formatStateUsing(fn (string $state): string => strtoupper(str_replace(' ', '', $state)))
                ->searchable(),
            TextColumn::make('gatheringPointFee.vehicle_type')
                ->label('Loại xe, trọng tải')
                ->limit(30)
                ->searchable(),
            TextColumn::make('liftingServiceFee.service_name')
                ->label('Dịch vụ nâng hạ')
                ->limit(30)
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('expected_in_at')
                ->label('Thời gian vào dự kiến')
                ->dateTime('d/m/Y H:i')
                ->searchable()
                ->sortable()
                ->toggleable(),
            TextColumn::make('status')
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
            ToggleColumn::make('is_priority')
                ->label('Ưu tiên')
                ->sortable()
                ->onIcon('heroicon-o-arrow-up')
                ->offIcon('heroicon-o-arrow-down')
                ->beforeStateUpdated(function ($record, $state) {
                    if ($record->status === 'approve' && $record->registrationEntry !== null) {
                        $record->registrationEntry->is_priority = $state;
                        $record->registrationEntry->save();
                    }
                }),
            TextColumn::make('approver.name')
                ->label('Người phê duyệt')
                ->badge()
                ->searchable(),
            TextColumn::make('approved_at')
                ->label('Thời gian phê duyệt')
                ->dateTime()
                ->sortable(),
            TextColumn::make('created_at')
                ->label('Ngày tạo')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
                ->label('Ngày cập nhật')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    private static function modifyQuery($query)
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('super_admin') || $user->hasRole('approve_vehicle')) {
            return $query->orderBy('sort', 'asc');
        }

        return $query->whereRaw('1 = 0');
    }

    private static function getFilters(): array
    {
        return [
            VehicleRegistrationFilter::make(),
        ];
    }

    private static function getRecordActions(): array
    {
        return [
            ApproveVehicleRegistrationAction::make(),
            ActionGroup::make([
                CancelApproveVehicleRegistrationAction::make(),
                EditAction::make()
                    ->slideOver()
                    ->mutateRecordDataUsing(
                        function (array $data, Model $record): array {
                            $data['has_lifting_service'] = $record->lifting_service_fee_id !== null;
                            $data['wants_invoice'] = $record->company_id !== null;

                            return $data;
                        }
                    )
                    ->mutateDataUsing(
                        function (array $data, Model $record): array {
                            unset($data['has_lifting_service']);
                            unset($data['wants_invoice']);

                            return $data;
                        }
                    )
                    ->label('Sửa'),
                DeleteAction::make(),
            ])->icon('heroicon-m-adjustments-vertical')
                ->size(Size::Small)
                ->iconButton()
                ->color('gray'),
        ];
    }

    private static function getToolbarActions(): array
    {
        return [
            DeleteBulkAction::make(),
            ExportBulkAction::make()
                ->label('Xuất Excel')
                ->modalHeading('Xuất Excel Đăng ký xe kiểm hoá')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('success')
                ->fileName(fn (Export $export): string => "Danh sách Đăng ký xe kiểm hoá-{$export->getKey()}.csv")
                ->exporter(VehicleRegistrationExporter::class),
        ];
    }
}
