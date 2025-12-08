<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages;
use App\Filament\Resources\CardResource\RelationManagers;
use App\Models\Card;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class CardResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Card::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Danh sách thẻ';
    protected static ?string $navigationGroup = 'Quản lý danh mục';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('account_id')
                    ->label('Mã số thẻ')
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
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('card_number')
                    ->label('Mã số thẻ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('card_name')
                    ->label('Tên thẻ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->searchable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'blocked' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'active' => 'Đang sử dụng',
                        'inactive' => 'Chưa sử dụng',
                        'blocked' => 'Bị khóa',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Chỉnh sửa thẻ'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListCards::route('/'),
            // 'create' => Pages\CreateCard::route('/create'),
            // 'edit' => Pages\EditCard::route('/{record}/edit'),
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