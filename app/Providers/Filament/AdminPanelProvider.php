<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureUserIsSubscribed;
use App\Livewire\BusinessInfoComponent;
use App\Livewire\KycComponent;
use App\Livewire\OnlineSalesComponent;
use App\Livewire\PersonalInfo;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Notifications\Livewire\Notifications;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Enums\Alignment;
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
        Notifications::alignment(Alignment::Center);

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Admin\Pages\Auth\Login::class)
            ->registration(\App\Filament\Admin\Pages\Auth\Register::class)
            ->passwordReset()
            ->emailVerification(\App\Filament\Admin\Pages\Auth\EmailVerificationPrompt::class)
            ->colors([
                'primary' => '#4338CA',
                'dark' => '#000000',
                'light' => '#FFFFFF',
                'secondary' => '#E5E5E5',
                'accent' => '#FCA311',
            ])
            ->font('Parkinsans', provider: GoogleFontProvider::class)
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([])
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
                EnsureUserIsSubscribed::class,
            ], true)
            ->plugins([
                \Jeffgreco13\FilamentBreezy\BreezyCore::make()
                    ->myProfile(slug: 'profile')
                    ->myProfileComponents([
                        'personal_info' => PersonalInfo::class,
                        BusinessInfoComponent::class,
                        KycComponent::class,
                        OnlineSalesComponent::class,
                    ])
                    ->enableTwoFactorAuthentication(force: true),
                \Swis\Filament\Backgrounds\FilamentBackgroundsPlugin::make()
                    ->showAttribution(false)
                    ->remember(900)
                    ->imageProvider(\Swis\Filament\Backgrounds\ImageProviders\Triangles::make()),
                \Awcodes\LightSwitch\LightSwitchPlugin::make(),
            ])
            ->spa()
            ->spaUrlExceptions([
                url('cashier/login'),
                url('admin/billing'),
            ])
            ->unsavedChangesAlerts()
            ->databaseTransactions()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->favicon(asset('favicon.svg'))
            ->brandLogo(asset('images/logo-dark.svg'))
            ->darkModeBrandLogo(asset('images/logo-light.svg'))
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Business Operations')
                    ->icon('heroicon-o-cursor-arrow-ripple'),
                NavigationGroup::make()
                    ->label('External Stakeholders')
                    ->icon('heroicon-o-square-3-stack-3d'),
                NavigationGroup::make()
                    ->label('Human Resources')
                    ->icon('heroicon-o-briefcase'),
                NavigationGroup::make()
                    ->label('Report Center')
                    ->icon('heroicon-o-presentation-chart-bar'),
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Billing')
                    ->url(fn (): string => route('billing.index'))
                    ->icon('heroicon-s-credit-card')
                    ->color('success'),
            ]);
    }
}
