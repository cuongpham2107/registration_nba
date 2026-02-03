<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarCatalogResource\Pages;
use App\Filament\Resources\CarCatalogResource\RelationManagers;
use App\Models\CarCatalog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Grouping\Group;

class CarCatalogResource extends Resource
{
    protected static ?string $model = CarCatalog::class;

     protected static ?string $modelLabel = 'Danh mục xe';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Danh mục xe';

    protected static ?string $navigationGroup = 'Quản lý danh mục';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('license_plate')
                    ->label('Biển số xe')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('unit_id')
                    ->label('Mã đơn vị')
                    ->required()
                    ->relationship('unit', 'name'),
                Forms\Components\Radio::make('billing_type')
                    ->label('Loại thanh toán')
                    ->options([
                        'prepaid' => 'Thu trước',
                        'postpaid' => 'Thu sau',
                    ])
                    ->required(),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Biển số xe')
                    ->alignCenter()
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit.name')
                    ->label('Đơn vị')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('billing_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'prepaid' ? 'Thu trước' : 'Thu sau')
                    ->alignCenter()
                    ->label('Loại thanh toán'),
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
            ->defaultGroup('unit.name')
            ->groups([
                Group::make('unit.name')
                    ->label('Đơn vị'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modal()
                    ->modalHeading('Chỉnh sửa danh mục xe')
                    ->modalDescription('Nhập thông tin xe cần chỉnh sửa'),
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
            'index' => Pages\ListCarCatalogs::route('/'),
            // 'create' => Pages\CreateCarCatalog::route('/create'),
            // 'edit' => Pages\EditCarCatalog::route('/{record}/edit'),
        ];
    }
}
