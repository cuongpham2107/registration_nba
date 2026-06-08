<?php

namespace App\Filament\Resources\RegistrationEntries;

use App\Filament\Resources\RegistrationEntries\Pages\ListRegistrationEntries;
use App\Filament\Resources\RegistrationEntries\Schemas\RegistrationEntryForm;
use App\Filament\Resources\RegistrationEntries\Tables\RegistrationEntriesTable;
use App\Models\RegistrationEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RegistrationEntryResource extends Resource
{
    protected static ?string $model = RegistrationEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $modelLabel = 'Ra vào';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Danh sách kiểm hoá';

    protected static ?string $title = 'Danh sách kiểm hoá';

    protected static ?int $navigationSort = 1;

    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Schema $schema): Schema
    {
        return RegistrationEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegistrationEntriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegistrationEntries::route('/'),
        ];
    }
}
