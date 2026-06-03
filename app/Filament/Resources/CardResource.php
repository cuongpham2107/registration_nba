<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages;
use App\Models\Card;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CardResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Card::class;

    protected static ?string $modelLabel = 'Thẻ';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Danh sách thẻ';

    protected static ?string $navigationGroup = 'Quản lý danh mục';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('card_number')
                    ->label('Số thẻ')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('card_name')
                    ->label('Tên thẻ')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('Loại thẻ')
                    ->options([
                        'daily' => 'Thẻ ngày',
                        'long_term' => 'Thẻ dài hạn',
                    ])
                    ->default('daily')
                    ->required()
                    ->live(),
                Forms\Components\DatePicker::make('expiry_date')
                    ->label('Ngày hết hạn')
                    ->placeholder('Chọn ngày hết hạn')
                    ->native(true)
                    ->prefixIcon('heroicon-o-calendar')
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
                    ->label('Số thẻ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('card_name')
                    ->label('Tên thẻ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Loại thẻ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'daily' => 'info',
                        'long_term' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'daily' => 'Thẻ ngày',
                        'long_term' => 'Thẻ dài hạn',
                    }),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Ngày hết hạn')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'blocked' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
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
                Tables\Actions\DeleteAction::make(),
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
