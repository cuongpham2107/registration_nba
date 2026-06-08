<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::getColumns())
            ->modifyQueryUsing(fn ($query) => self::modifyQuery($query))
            ->recordActions(self::getRecordActions())
            ->toolbarActions(self::getBulkActions());
    }

    private static function getColumns(): array
    {
        return [
            ImageColumn::make('avatar')
                ->label('Avatar')
                ->width(40)
                ->circular()
                ->searchable(),
            TextColumn::make('full_name')
                ->label('Họ và tên')
                ->searchable(),
            TextColumn::make('username')
                ->label('Tài khoản')
                ->searchable(),
            TextColumn::make('email')
                ->label('Địa chỉ Email')
                ->searchable(),
            TextColumn::make('mobile_phone')
                ->label('Số điện thoại')
                ->searchable(),
            TextColumn::make('roles.name')
                ->label('Quyền')
                ->badge()
                ->sortable(),
            TextColumn::make('approverConfigs.approver.full_name')
                ->label('Người phê duyệt')
                ->badge(),
            TextColumn::make('created_at')
                ->label('Ngày tạo')
                ->dateTime()
                ->sortable(),
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

        return $query->where('id', $user->id);
    }

    private static function getRecordActions(): array
    {
        return [
            Action::make('grant_role_and_approver')
                ->label('Cấp quyền & Người phê duyệt')
                ->icon('heroicon-o-shield-check')
                ->modal()
                ->modalWidth('md')
                ->hidden(fn ($record) => ! Auth::user()?->hasRole('super_admin'))
                ->fillForm(function ($record): array {
                    // Load role hiện tại của user để pre-select
                    $currentRoleIds = DB::connection('mysql')
                        ->table('model_has_roles')
                        ->where('model_type', User::class)
                        ->where('model_id', $record->id)
                        ->pluck('role_id')
                        ->toArray();

                    // Load danh sách người phê duyệt hiện tại
                    $approverIds = DB::connection('mysql')
                        ->table('user_approvers')
                        ->where('user_id', $record->id)
                        ->pluck('approver_id')
                        ->toArray();

                    return [
                        'roles' => $currentRoleIds,
                        'approver_ids' => $approverIds,
                    ];
                })
                ->schema([
                    Select::make('roles')
                        ->label('Vai trò')
                        ->options(function () {
                            return DB::connection('mysql')
                                ->table('roles')
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->multiple()
                        ->preload()
                        ->searchable(),
                    Select::make('approver_ids')
                        ->label('Người phê duyệt')
                        ->multiple()
                        ->options(function () {
                            return DB::connection('id_db')
                                ->table('users')
                                ->where('is_active', true)
                                ->orderBy('full_name')
                                ->get(['id', 'full_name', 'username'])
                                ->mapWithKeys(function ($user): array {
                                    $label = "{$user->full_name} - {$user->username}";

                                    return [$user->id => $label];
                                })
                                ->toArray();
                        })
                        ->searchable()
                        ->preload(),
                ])
                ->action(function ($record, array $data): void {
                    // Xử lý roles
                    $roleNames = DB::connection('mysql')
                        ->table('roles')
                        ->whereIn('id', $data['roles'] ?? [])
                        ->pluck('name')
                        ->toArray();

                    $record->syncRoles($roleNames);

                    // Xử lý approver — xoá cũ và ghi lại nhiều người phê duyệt
                    $approverIds = $data['approver_ids'] ?? [];

                    DB::connection('mysql')
                        ->table('user_approvers')
                        ->where('user_id', $record->id)
                        ->delete();

                    $now = now();
                    foreach ($approverIds as $approverId) {
                        DB::connection('mysql')
                            ->table('user_approvers')
                            ->insert([
                                'user_id' => $record->id,
                                'approver_id' => $approverId,
                                'updated_at' => $now,
                                'created_at' => $now,
                            ]);
                    }

                    Notification::make()
                        ->title('Cập nhật vai trò & người phê duyệt thành công')
                        ->success()
                        ->send();
                }),

        ];
    }

    private static function getBulkActions(): array
    {
        return [
            DeleteBulkAction::make(),
        ];
    }
}
