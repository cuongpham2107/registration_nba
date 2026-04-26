<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Login;
use App\Filament\Pages\UserProfile;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Guava\Tutorials\TutorialsPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use WatheqAlshowaiter\FilamentStickyTableHeader\StickyTableHeaderPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->login(Login::class)
            ->userMenuItems([
                MenuItem::make()
                    ->label('Trang cá nhân')
                    ->url(fn (): string => UserProfile::getUrl())
                    ->icon('heroicon-o-user-circle'),
            ])
            ->databaseNotifications()
            ->colors([
                'primary' => Color::Indigo,
            ])
            // ->font('Roboto', provider: LocalFontProvider::class)
            ->brandName('ASG')
            ->brandLogo(asset('images/favicon.ico'))
            ->favicon(asset('images/favicon.ico'))
            ->brandLogoHeight('2rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->spa()
            ->topNavigation()
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->maxContentWidth(MaxWidth::Full)
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 2,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
                TutorialsPlugin::make(),
                \Rmsramos\Activitylog\ActivitylogPlugin::make()
                    ->navigationGroup('Hệ thống')
                    ->navigationCountBadge(true)
                    ->label('Nhật ký hoạt động')
                    ->pluralLabel('Nhật ký hoạt động')
                    ->translateSubject(fn ($label) => __('models.'.$label))
                    ->navigationSort(3)
                    ->navigationIcon('heroicon-o-book-open')
                    ->authorize(fn () => auth()->user()?->can('view_any_activitylog')),
                //  StickyTableHeaderPlugin::make(),
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
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
