<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuestCardResource\Pages;
use App\Models\GuestCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GuestCardResource extends Resource
{
    protected static ?string $model = GuestCard::class;

    protected static ?string $modelLabel = 'Thẻ khách';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Thẻ khách';

    protected static ?string $navigationGroup = 'Quản lý danh mục';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                Forms\Components\DatePicker::make('issued_at')
                    ->label('Ngày cấp'),
                Forms\Components\TextInput::make('issue_area')
                    ->label('Khu vực cấp')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                    ->label('Hết hạn')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('issue_area')
                    ->label('Khu vực cấp'),
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
            ->defaultSort('card_number')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modal()
                    ->modalHeading('Chỉnh sửa thẻ khách')
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuestCards::route('/'),
        ];
    }
}
