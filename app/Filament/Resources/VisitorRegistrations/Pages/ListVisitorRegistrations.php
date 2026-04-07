<?php

namespace App\Filament\Resources\VisitorRegistrations\Pages;

use App\Filament\Resources\VisitorRegistrations\VisitorRegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;

class ListVisitorRegistrations extends ListRecords
{
    protected static string $resource = VisitorRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Đăng ký khách mới')
                ->icon('heroicon-o-plus')
                ->modalWidth(Width::SixExtraLarge)
                ->modalHeading('Đăng ký khách mới')
                // Only disabled if user has an approver
                ->disabled(fn () => Auth::user() && ! Auth::user()->approver)
                ->mutateDataUsing(function (array $data): array {
                    $user = Auth::user();
                    if ($user && $user->approver) {
                        $data['user_id'] = $user->id;
                        $data['approver_id'] = $user->approver->id;
                    }

                    return $data;
                }),
        ];
    }
}
