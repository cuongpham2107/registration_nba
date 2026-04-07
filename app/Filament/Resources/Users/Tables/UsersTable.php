<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

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
            TextColumn::make('name')
                ->label('Họ và tên')
                ->searchable(),
            TextColumn::make('username')
                ->label('Tài khoản')
                ->searchable(),
            TextColumn::make('email')
                ->label('Địa chỉ Email')
                ->searchable(),
            TextColumn::make('department_name')
                ->label('Phòng ban')
                ->searchable(),
            TextColumn::make('roles.name')
                ->label('Quyền')
                ->badge()
                ->sortable(),
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
            EditAction::make(),
        ];
    }

    private static function getBulkActions(): array
    {
        return [
            DeleteBulkAction::make(),
        ];
    }
}
