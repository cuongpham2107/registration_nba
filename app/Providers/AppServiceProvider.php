<?php

namespace App\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

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
        URL::forceScheme('https');
        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));
        }

        Lang::addNamespace('filament-panels', resource_path('lang/vendor/filament-panels'));
        FilamentAsset::register([
            Css::make('custom', asset(path: 'css/filament/custom.css?version=12312')),
        ]);
    }
}
