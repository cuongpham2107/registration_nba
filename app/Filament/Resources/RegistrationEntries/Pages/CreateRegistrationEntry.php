<?php

namespace App\Filament\Resources\RegistrationEntries\Pages;

use App\Filament\Resources\RegistrationEntries\RegistrationEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRegistrationEntry extends CreateRecord
{
    protected static string $resource = RegistrationEntryResource::class;
}
