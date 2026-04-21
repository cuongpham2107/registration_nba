<?php

namespace App\Filament\Pages;

use App\Filament\Resources\RegistrationEntries\Tables\RegistrationEntriesTable;
use App\Models\RegistrationEntry;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RegistrationPeople extends Page implements HasActions, HasSchemas, HasTable
{
    use HasPageShield;
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected string $view = 'filament.pages.registration-people';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $modelLabel = 'Khách ra vào';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Danh sách khách ra vào';

    protected static ?string $title = 'Danh sách khách ra vào';

    // public static function canAccess(): bool
    // {
    //     return auth()->user()->canManageSettings();
    // }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => RegistrationEntry::query()->where('type', 'working'))
            ->columns(RegistrationEntriesTable::columnsForPage())
            ->filters(RegistrationEntriesTable::filters())
            ->defaultSort('created_at', 'asc')
            ->defaultPaginationPageOption(25)
            ->recordActions(RegistrationEntriesTable::recordActions(), position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                // ...
            ]);
    }
}
