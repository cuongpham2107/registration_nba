<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Login;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->login(Login::class)
            // ->login()
            ->databaseNotifications()
            ->colors([
                'primary' => Color::Indigo,
                'secondary' => Color::Gray,
                'success' => Color::Green,
                'danger' => Color::Red,
                'warning' => Color::Yellow,
                'info' => Color::Blue,
            ])
            ->maxContentWidth(Width::Full)
            ->brandName('ASGL')
            ->brandLogo(asset('images/ASG.png'))
            ->favicon(asset('images/ASG.png'))
            ->brandLogoHeight('2rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Dashboard::class,
            ])
            // ->homeUrl('/admin/dashboard')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->spa()
            ->topNavigation()
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => '<script>
                    window.printFile = function (url) {
                        const iframe = document.createElement("iframe");
                        iframe.style.position = "fixed";
                        iframe.style.right = "0";
                        iframe.style.bottom = "0";
                        iframe.style.width = "0";
                        iframe.style.height = "0";
                        iframe.style.border = "0";
            
                        iframe.src = url;
                        document.body.appendChild(iframe);
            
                        iframe.onload = function () {
                            iframe.contentWindow.focus();
                            iframe.contentWindow.print();
                            
                            setTimeout(() => {
                                document.body.removeChild(iframe);
                            }, 5000);
                        };
                    };
                </script>'
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => '<script>
                (function () {
                    let buffer = \'\';
                    document.addEventListener(\'keydown\', function (e) {
                        if (e.key === \'Enter\') {
                            if (buffer.length > 5) {
                            console.log(buffer)
                                Livewire.dispatch("card-scanned", { code: buffer });
                            }
                            buffer = \'\';
                        } else if (e.key.length === 1) {
                            buffer += e.key;
                        }
                    });
                })();
            </script>'
            )
            ->plugins([
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
