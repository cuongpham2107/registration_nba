<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceListResource\Pages;
use App\Filament\Resources\PriceListResource\RelationManagers;
use App\Models\PriceList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PriceListResource extends Resource
{
    protected static ?string $model = PriceList::class;
    protected static ?string $modelLabel = 'Bảng giá';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Bảng giá';

    protected static ?string $navigationGroup = 'Quản lý danh mục';
    protected static ?int $navigationSort = 4;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('vehicle_type')
                    ->label('Loại xe')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ticket_code')
                    ->label('Mã vé')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('base_fee_120min')
                    ->label('Phí khai thác(áp dụng cho block 120 phút đầu)')
                    ->required()
                    ->numeric()
                    ->suffix('VNĐ')
                    ->default(0),
                Forms\Components\TextInput::make('additional_fee_30min')
                    ->label('Phí khai thác sau 120 phút (áp dụng cho mỗi block 30 phút)')
                    ->required()
                    ->numeric()
                    ->suffix('VNĐ')
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label('Loại xe')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ticket_code')
                    ->label('Mã vé')
                    ->searchable(),
                Tables\Columns\TextColumn::make('base_fee_120min')
                    ->label('Phí khai thác(áp dụng cho block 120 phút đầu)')
                    ->alignCenter()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('additional_fee_30min')
                    ->label('Phí khai thác sau 120 phút (áp dụng cho mỗi block 30 phút)')
                    ->alignCenter()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modal()
                    ->modalHeading('Chỉnh sửa bảng giá')
                    ->modalDescription('Nhập thông tin bảng giá cần chỉnh sửa'),
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
            'index' => Pages\ListPriceLists::route('/'),
            // 'create' => Pages\CreatePriceList::route('/create'),
            // 'edit' => Pages\EditPriceList::route('/{record}/edit'),
        ];
    }
}
