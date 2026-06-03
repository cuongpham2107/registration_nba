<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlackListResource\Pages;
use App\Filament\Resources\BlackListResource\RelationManagers;
use App\Models\BlackList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BlackListResource extends Resource
{
    protected static ?string $model = BlackList::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    protected static ?string $modelLabel = 'Danh sách đen';

    protected static ?string $navigationLabel = 'Danh sách đen';

    protected static ?string $navigationGroup = 'Quản lý danh mục';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('registration_vehicle_id')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('reason')
                    ->label('Lý do')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('blacklisted_by')
                    ->label('Người thêm vào danh sách đen')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('blacklisted_at')
                    ->label('Thời gian thêm vào danh sách đen')
                    ->placeholder('Chọn ngày, giờ')
                    ->native(true)
                    ->prefixIcon('heroicon-o-calendar'),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registrationVehicle.driver_name')
                    ->label('Tên tài xế')
                    ->sortable(),
                 Tables\Columns\TextColumn::make('registrationVehicle.driver_phone')
                    ->label('Số điện thoại')
                    ->sortable(),
                Tables\Columns\TextColumn::make('registrationVehicle.vehicle_number')
                    ->label('Biển số xe')
                    ->sortable(),
                Tables\Columns\TextColumn::make('registrationVehicle.secret')
                    ->label('Mã bí mật')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('blacklistedBy.name')
                    ->label('Người thêm vào danh sách đen')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('blacklisted_at')
                    ->label('Thời gian thêm vào danh sách đen')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Còn hiệu lực')
                    ->boolean(),
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
                // Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('removeFromBlacklist')
                    ->label('Gỡ khỏi danh sách đen')
                    ->color('success')
                    ->icon('heroicon-o-lock-open')
                    ->hidden(fn ($record) => ! auth()->user() || (! auth()->user()->hasRole('super_admin') && ! auth()->user()->hasRole('approve_vehicle')) || !$record->is_active)
                    ->action(function (BlackList $record) {
                        $record->update([
                            'is_active' => false,
                        ]);
                    }),
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
            'index' => Pages\ListBlackLists::route('/'),
            'create' => Pages\CreateBlackList::route('/create'),
            'edit' => Pages\EditBlackList::route('/{record}/edit'),
        ];
    }
}
