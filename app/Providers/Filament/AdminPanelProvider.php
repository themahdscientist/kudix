<?php

namespace App\Providers\Filament;

use App\Livewire\CompanyInfoComponent;
use App\Livewire\PersonalInfo;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Admin\Pages\Auth\Login::class)
            ->registration(\App\Filament\Admin\Pages\Auth\Register::class)
            ->passwordReset()
            ->emailVerification(\App\Filament\Admin\Pages\Auth\EmailVerificationPrompt::class)
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
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
                Css::make('admin-indicator', resource_path('css/admin-indicator.css')),
            ])
            ->authMiddleware([
                Authenticate::class,
                // UploadLogo::class,
            ])
            ->plugins([
                \Jeffgreco13\FilamentBreezy\BreezyCore::make()
                    ->myProfile(slug: 'profile')
                    ->myProfileComponents([
                        'personal_info' => PersonalInfo::class,
                        CompanyInfoComponent::class,
                    ])
                    ->enableTwoFactorAuthentication(),
                // \pxlrbt\FilamentSpotlight\SpotlightPlugin::make(),
                \Swis\Filament\Backgrounds\FilamentBackgroundsPlugin::make()
                    ->showAttribution(false)
                    ->remember(900)
                    ->imageProvider(\Swis\Filament\Backgrounds\ImageProviders\Triangles::make()),
                // \Awcodes\Recently\RecentlyPlugin::make(),
                \Awcodes\LightSwitch\LightSwitchPlugin::make(),
            ])
            ->spa()
            ->spaUrlExceptions([url('/app/login')])
            ->unsavedChangesAlerts()
            ->databaseTransactions()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->favicon(asset('favicon.ico'))
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('External Stakeholders')
                    ->icon('heroicon-o-square-3-stack-3d'),
                NavigationGroup::make()
                    ->label('Business Operations')
                    ->icon('heroicon-o-cursor-arrow-ripple'),
                NavigationGroup::make()
                    ->label('Human Resources')
                    ->icon('heroicon-o-briefcase'),
            ]);
    }
}
