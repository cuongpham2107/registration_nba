<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Guava\Tutorials\Concerns\InteractsWithTutorials;
use Guava\Tutorials\Contracts\HasTutorials;
use Guava\Tutorials\Steps\Step;
use Guava\Tutorials\Tutorial;

class CreateCard extends CreateRecord 
{
    // use InteractsWithTutorials;
    protected static string $resource = CardResource::class;

    public function mount(): void
    {
        parent::mount();
        
    }
 
    
}
