<?php

namespace App\Policies;

use Filament\Actions\Exports\Models\Export;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Support\Facades\DB;

class ExportPolicy
{
    use HandlesAuthorization;

    public function view(AuthUser $user, Export $export): bool
    {
        // User model uses 'id_db' connection, but the exports table uses 'mysql'
        // with user_id referencing the local users table.
        // Map the authenticated user to their local mysql user ID via asgl_id column.
        $localUserId = DB::connection('mysql')
            ->table('users')
            ->where('id', $user->getAuthIdentifier())
            ->orWhere('asgl_id', (string) $user->getAuthIdentifier())
            ->value('id');

        return $localUserId !== null && (int) $localUserId === (int) $export->user_id;
    }
}
