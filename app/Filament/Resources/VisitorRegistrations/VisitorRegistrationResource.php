<?php

namespace App\Filament\Resources\VisitorRegistrations;

use App\Filament\Resources\VisitorRegistrations\Pages\ListVisitorRegistrations;
use App\Filament\Resources\VisitorRegistrations\Schemas\VisitorRegistrationForm;
use App\Filament\Resources\VisitorRegistrations\Tables\VisitorRegistrationsTable;
use App\Models\VisitorRegistration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VisitorRegistrationResource extends Resource
{
    protected static ?string $model = VisitorRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $modelLabel = 'Đăng ký khách';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Đăng ký khách';

    protected static ?string $title = 'Đăng ký khách';

    protected static ?int $navigationSort = 1;

    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Schema $schema): Schema
    {
        return VisitorRegistrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisitorRegistrationsTable::configure($table);
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
            'index' => ListVisitorRegistrations::route('/'),
            // 'create' => CreateVisitorRegistration::route('/create'),
            // 'edit' => EditVisitorRegistration::route('/{record}/edit'),
        ];
    }
}
