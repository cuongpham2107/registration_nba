<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateRegistration extends CreateRecord
{
    protected static string $resource = RegistrationResource::class;

    
    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $data['user_id'] = Auth::user()->id;
    //     dd($data);
    //     return $data;
    // }
}
