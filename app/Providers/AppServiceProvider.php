<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\Role;
use App\Policies\ExportPolicy;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        app(PermissionRegistrar::class)
            ->setPermissionClass(Permission::class)
            ->setRoleClass(Role::class);

        // Register ExportPolicy for Filament export download authorization
        Gate::policy(Export::class, ExportPolicy::class);

        // Map user_id from id_db to local mysql user ID for export records
        // This is needed because User model uses 'id_db' connection, but the
        // exports table is on 'mysql' connection with a FK to users.id (local).
        Export::creating(function (Export $export): void {
            $localUserId = DB::connection('mysql')
                ->table('users')
                ->where('id', $export->user_id)
                ->orWhere('asgl_id', (string) $export->user_id)
                ->value('id');

            if ($localUserId) {
                $export->user_id = $localUserId;
            }
        });
    }
}
