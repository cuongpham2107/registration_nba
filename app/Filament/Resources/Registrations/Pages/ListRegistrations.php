<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Filament\Resources\Registrations\Schemas\RegistrationForm;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(Auth::user()->hasRole('working') ? 'Đăng ký khách mới' : 'Đăng ký xe kiểm hoá')
                ->icon('heroicon-o-plus')
                ->hidden(fn () => Auth::user() ? ! Auth::user()->hasRole(['inspection', 'working']) : true)
                ->modalWidth(Width::ScreenTwoExtraLarge)
                ->modalHeading('Đăng ký khách mới')
                ->schema(fn (Schema $schema) => RegistrationForm::configure($schema, Auth::user()->roles->pluck('name')->first()))
                ->mutateDataUsing(function (array $data): array {
                    $user = Auth::user();
                    if ($user && $user->approver) {
                        $data['user_id'] = $user->id;
                        $data['approver_id'] = $user->approver->id;
                    } else {
                        $data['user_id'] = $user->id;
                        $data['approver_id'] = null;
                    }

                    return $data;
                }),
        ];
    }
}
