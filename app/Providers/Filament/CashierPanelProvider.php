<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureUserIsSubscribed;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CashierPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('cashier')
            ->path('cashier')
            ->login(\App\Filament\Cashier\Pages\Auth\Login::class)
            ->colors([
                'primary' => '#FCA311',
                'dark' => '#000000',
                'light' => '#FFFFFF',
            ])
            ->font('Parkinsans', provider: GoogleFontProvider::class)
            ->discoverResources(in: app_path('Filament/Cashier/Resources'), for: 'App\\Filament\\Cashier\\Resources')
            ->discoverPages(in: app_path('Filament/Cashier/Pages'), for: 'App\\Filament\\Cashier\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Cashier/Widgets'), for: 'App\\Filament\\Cashier\\Widgets')
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
            ->assets([
                Css::make('cashier-indicator', resource_path('css/cashier-indicator.css')),
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureUserIsSubscribed::class,
            ])
            ->plugins([
                \Swis\Filament\Backgrounds\FilamentBackgroundsPlugin::make()
                    ->showAttribution(false)
                    ->remember(900)
                    ->imageProvider(\Swis\Filament\Backgrounds\ImageProviders\Triangles::make()),
                \Awcodes\LightSwitch\LightSwitchPlugin::make(),
            ])
            ->spa()
            ->spaUrlExceptions([
                url('admin/login'),
                url('admin/register'),
            ])
            ->unsavedChangesAlerts()
            ->databaseTransactions()
            ->favicon(asset('favicon.svg'))
            ->brandLogo(asset('images/logo-dark.svg'))
            ->darkModeBrandLogo(asset('images/logo-light.svg'))
            ->sidebarCollapsibleOnDesktop();
    }
}
