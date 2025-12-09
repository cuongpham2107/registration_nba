<?php

namespace App\Filament\Resources;


use Filament\Tables\Actions\ImportAction;
use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Imports\CustomerImporter;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class CustomerResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Customer::class;
     protected static ?string $modelLabel = 'Khách';

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Danh sách khách';
    protected static ?string $navigationGroup = 'Quản lý danh mục';

    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->Label('Tên khách')
                    ->required(),
                Forms\Components\TextInput::make('papers')
                    ->Label('Giấy tờ')
                    ->required(),
                Forms\Components\TextInput::make('type')
                    ->Label('Loại')
                    ->required(),
                // Forms\Components\TextInput::make('area')
                //     ->Label('Khu vực')
                //     ->required(),
                Forms\Components\TextInput::make('license_plate')
                    ->Label('Biển số')
                    ->required(),
                Forms\Components\TextInput::make('note')
                    ->Label('Ghi chú'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên khách')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('papers')
                    ->label('Giấy tờ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Loại') 
                    ->searchable(),
                // Tables\Columns\TextColumn::make('area')
                //     ->label('Khu vực')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Biển số')
                    ->searchable(),
                Tables\Columns\TextColumn::make('note')
                    ->label('Ghi chú')
                    ->searchable(),
            ])
            ->headerActions([
               
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCustomers::route('/'),
            // 'create' => Pages\CreateCustomer::route('/create'),
            // 'edit' => Pages\EditCustomer::route('/{record}/edit'),
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