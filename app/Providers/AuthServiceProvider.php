<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \Spatie\Permission\Models\Role::class => \App\Policies\RolePolicy::class,
        \App\Models\Area::class => \App\Policies\AreaPolicy::class,
        \App\Models\Card::class => \App\Policies\CardPolicy::class,
        \App\Models\Customer::class => \App\Policies\CustomerPolicy::class,
        \App\Models\Registration::class => \App\Policies\RegistrationPolicy::class,
        \App\Models\RegisterDirectly::class => \App\Policies\RegisterDirectlyPolicy::class,
        \App\Models\RegistrationVehicle::class => \App\Policies\RegistrationVehiclePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
