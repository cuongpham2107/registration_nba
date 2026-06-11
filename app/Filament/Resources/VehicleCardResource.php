<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleCardResource\Pages;
use App\Models\VehicleCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VehicleCardResource extends Resource
{
    protected static ?string $model = VehicleCard::class;

    protected static ?string $modelLabel = 'Thẻ xe';

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Thẻ xe';

    protected static ?string $navigationGroup = 'Quản lý danh mục';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('stt')
                    ->label('STT')
                    ->numeric(),
                Forms\Components\TextInput::make('full_name')
                    ->label('Họ và tên')
                    ->maxLength(255),
                Forms\Components\TextInput::make('unit')
                    ->label('Đơn vị')
                    ->maxLength(255),
                Forms\Components\TextInput::make('unit_abbr')
                    ->label('Đơn vị viết tắt')
                    ->maxLength(255),
                Forms\Components\TextInput::make('title')
                    ->label('Chức danh')
                    ->maxLength(255),
                Forms\Components\TextInput::make('card_number')
                    ->label('Mã số thẻ')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('issued_at')
                    ->label('Ngày cấp'),
                Forms\Components\TextInput::make('issue_area')
                    ->label('Khu vực cấp')
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Điện thoại')
                    ->maxLength(255),
                Forms\Components\TextInput::make('license_plate')
                    ->label('Biển kiểm soát')
                    ->maxLength(255),
                Forms\Components\TextInput::make('vehicle_type')
                    ->label('Loại xe')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stt')
                    ->label('STT')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Họ và tên')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Đơn vị')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('unit_abbr')
                    ->label('Đơn vị viết tắt')
                    ->badge()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Chức danh')
                    ->searchable(),
                Tables\Columns\TextColumn::make('card_number')
                    ->label('Mã số thẻ')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Đã sao chép'),
                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Ngày cấp')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('issue_area')
                    ->label('Khu vực cấp'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Điện thoại')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Biển kiểm soát')
                    ->searchable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label('Loại xe')
                    ->searchable(),
                Tables\Columns\TextColumn::make('source_section')
                    ->label('Nguồn')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('stt')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modal()
                    ->modalHeading('Chỉnh sửa thẻ xe')
                    ->modalDescription('Nhập thông tin cần chỉnh sửa'),
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
            'index' => Pages\ListVehicleCards::route('/'),
            // 'create' => Pages\CreateVehicleCard::route('/create'),
            // 'edit' => Pages\EditVehicleCard::route('/{record}/edit'),
        ];
    }
}
