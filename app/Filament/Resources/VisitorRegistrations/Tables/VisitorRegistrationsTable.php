<?php

namespace App\Filament\Resources\VisitorRegistrations\Tables;

use App\Filament\Exports\VisitorRegistrationExporter;
use App\Filament\Resources\VisitorRegistrations\Actions\ApproveVisitorRegistrationAction;
use App\Filament\Resources\VisitorRegistrations\Actions\RefuseVisitorRegistrationAction;
use App\Filament\Resources\VisitorRegistrations\Actions\SendMailVisitorRegistrationAction;
use App\Filament\Resources\VisitorRegistrations\Filters\VisitorRegistrationFilter;
use App\Models\VisitorRegistration;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class VisitorRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->header(view('filament.resources.tables.header'))
            ->description(self::getDescription())
            ->emptyStateHeading('Không có đơn đăng ký khách nào')
            ->emptyStateIcon('heroicon-o-user')
            ->emptyStateDescription('Hiện tại chưa có đơn đăng ký khách nào được tạo. Vui lòng nhấn nút "Đăng ký khách mới" để tạo mới.')
            ->columns(self::getColumns())
            ->recordClasses(fn (Model $record) => match ($record->status) {
                'sent' => 'border-s-2 border-orange-600 dark:border-orange-300',
                default => '',
            })
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->modifyQueryUsing(fn ($query) => self::modifyQuery($query))
            ->filters(self::getFilters(), layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
            ->recordActions(self::getRecordActions(), position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions(self::getToolbarActions());
    }

    private static function getDescription(): ?HtmlString
    {
        $user = Auth::user();
        if (! $user || ! $user->hasRole('approver')) {
            return null;
        }

        $count = VisitorRegistration::where('approver_id', $user->id)
            ->where('status', 'sent')
            ->whereNull('type')
            ->count();

        if ($count > 0) {
            $filterUrl = route('filament.admin.resources.visitor-registrations.index', [
                'tableFilters' => [
                    'date_range' => [
                        'status' => 'sent',
                        'type' => 'none',
                    ],
                ],
            ]);

            return new HtmlString(
                '<a href="'.$filterUrl.'" style="display: flex; align-items: center; gap: 6px; padding: 8px 10px; background: linear-gradient(135deg, #ff6b6b 0%, #ffa5a5 100%); color: white; border-radius: 8px; font-weight: 500; font-size: 0.875rem; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform=\'translateY(-1px)\'; this.style.boxShadow=\'0 4px 12px rgba(255, 107, 107, 0.4)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'none\';">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px; flex-shrink: 0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                    <span>Bạn có <strong style="font-size: 1em; padding: 0 2px;">'.$count.'</strong> yêu cầu đăng ký đang chờ phê duyệt </span>
                </a>'
            );
        }

        return null;
    }

    private static function getColumns(): array
    {
        return [
            TextColumn::make('id')
                ->label('Mã ĐK')
                ->width('1%')
                ->alignCenter()
                ->sortable()
                ->searchable()
                ->toggleable(),
            TextColumn::make('name')
                ->label('Đơn vị khách')
                ->weight(FontWeight::Bold)
                ->width('15%')
                ->searchable()
                ->toggleable(),
            TextColumn::make('purpose')
                ->label('Mục đích')
                ->width('15%')
                ->limit(25)
                ->searchable()
                ->toggleable(),
            TextColumn::make('bks')
                ->label('BKS ô tô')
                ->weight(FontWeight::Bold)
                ->searchable()
                ->toggleable(),
            ColumnGroup::make('Thời gian dự kiến', [
                TextColumn::make('start_date')
                    ->label('Giờ vào')
                    ->dateTime('d/m/Y H:i')
                    ->searchable()
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('end_date')
                    ->label('Giờ ra')
                    ->dateTime('d/m/Y H:i')
                    ->searchable()
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),
            ])->alignCenter()->wrapHeader(),

            TextColumn::make('status')
                ->Label('Trạng thái')
                ->weight(FontWeight::Bold)
                ->badge()
                ->toggleable()
                ->color(fn (string $state): string => match ($state) {
                    'sent' => 'success',
                    'not_yet_sent' => 'danger',
                })
                ->formatStateUsing(fn (string $state) => match ($state) {
                    'sent' => 'Đã gửi',
                    'not_yet_sent' => 'Chưa gửi',
                }),
            TextColumn::make('type')
                ->Label('Duyệt')
                ->weight(FontWeight::ExtraBold)
                ->badge()
                ->toggleable()
                ->color(fn (string $state): string => match ($state) {
                    'browse' => 'success',
                    'refuse' => 'danger',
                })
                ->formatStateUsing(fn (string $state) => match ($state) {
                    'browse' => 'Duyệt',
                    'refuse' => 'Từ chối',
                }),
            TextColumn::make('type_date')
                ->Label('Ngày duyệt')
                ->dateTime('d/m/Y')
                ->searchable()
                ->sortable()
                ->toggleable(),
            TextColumn::make('approver.name')
                ->Label('Người duyệt')
                ->searchable()
                ->badge()
                ->color('warning')
                ->sortable()
                ->toggleable(),
            TextColumn::make('user.name')
                ->Label('Người tạo')
                ->badge()
                ->separator(',')
                ->limit(15)
                ->toggleable(),
            TextColumn::make('created_at')
                ->Label('Ngày tạo')
                ->date('d/m/Y')
                ->limit(20)
                ->toggleable(),
        ];
    }

    private static function modifyQuery($query)
    {
        $user = Auth::user();
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }
        if ($user->hasRole('super_admin')) {
            return $query;
        }
        if ($user->hasRole('approver')) {
            return $query->where('approver_id', $user->id);
        }

        return $query->where('user_id', $user->id);
    }

    private static function getFilters(): array
    {
        return [
            VisitorRegistrationFilter::make(),
        ];
    }

    private static function getRecordActions(): array
    {
        return [
            SendMailVisitorRegistrationAction::make(),
            ActionGroup::make([
                EditAction::make()
                    ->modalWidth(Width::SixExtraLarge)
                    ->hidden(fn (VisitorRegistration $record) => $record->status === 'sent' && $record->type === 'browse' || $record->type === 'refuse' || $record->user_id !== Auth::id()),
                ViewAction::make()->modalWidth(Width::SixExtraLarge),
                DeleteAction::make()
                    ->hidden(fn (VisitorRegistration $record) => $record->status === 'sent' && $record->type === 'browse' || $record->type === 'refuse' || $record->user_id !== Auth::id()),
                ApproveVisitorRegistrationAction::make(),
                RefuseVisitorRegistrationAction::make(),
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
                ->modalHeading('Xuất Excel đăng ký khách')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('success')
                ->fileName(fn (Export $export): string => "Danh sách đăng ký khách-{$export->getKey()}.csv")
                ->exporter(VisitorRegistrationExporter::class),
        ];
    }
}
