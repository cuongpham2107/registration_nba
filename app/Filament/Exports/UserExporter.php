<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('avatar')
                ->label('Avatar'),
            ExportColumn::make('full_name')
                ->label('Họ và tên'),
            ExportColumn::make('username')
                ->label('Tài khoản'),
            ExportColumn::make('email')
                ->label('Địa chỉ Email'),
            ExportColumn::make('mobile_phone')
                ->label('Số điện thoại'),
            ExportColumn::make('roles.name')
                ->label('Quyền')
                ->formatStateUsing(fn ($state): string => static::formatRoleNames($state)),
            ExportColumn::make('approverConfigs.approver.full_name')
                ->label('Người phê duyệt'),
            ExportColumn::make('created_at')
                ->label('Ngày tạo'),
        ];
    }

    /**
     * Format a single role name to Vietnamese label.
     */
    public static function formatRoleName(?string $roleName): string
    {
        return match ($roleName) {
            'super_admin' => 'SuperAdmin',
            'protect' => 'Bảo vệ kho 3',
            'protect_port_0' => 'Bảo vệ cổng chính',
            'accountant' => 'Kế toán',
            'Approver' => 'Người phê duyệt',
            'panel_user' => 'Người đăng ký',
            default => $roleName ?? '',
        };
    }

    /**
     * Format one or more role names (comma-separated) to Vietnamese labels.
     */
    public static function formatRoleNames(string|array|null $state): string
    {
        if (empty($state)) {
            return '';
        }

        if (is_array($state)) {
            $roleNames = $state;
        } else {
            $roleNames = explode(',', $state);
        }

        return collect($roleNames)
            ->map(fn (string $name): string => static::formatRoleName(trim($name)))
            ->filter()
            ->implode(', ');
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Xuất dữ liệu người dùng thành công với '.number_format($export->successful_rows).' '.str('bản ghi')->plural($export->successful_rows).'.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('bản ghi')->plural($failedRowsCount).' xuất thất bại.';
        }

        return $body;
    }

    public function getFormats(): array
    {
        return [
            ExportFormat::Xlsx,
        ];
    }
}
