<?php

namespace App\Filament\Admin\Pages\Auth;

use App\Jobs\FetchIpLocation;
use App\Jobs\FetchPaystackCountries;
use App\Models\Role;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\StaticAction;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class Register extends BaseRegister
{
    protected $listeners = ['refresh' => '$refresh'];

    protected ?string $maxWidth = '4xl';

    public ?array $countries = [];

    public ?string $ipLocation;

    public function mount(): void
    {
        if (filament()->auth()->check()) {
            redirect()->intended(filament()->getUrl());
        }

        $this->countries = $this->getPaystackSupportedCountries();

        $this->ipLocation = $this->getIpLocation();

        $this->callHook('beforeFill');

        $this->form->fill();

        $this->callHook('afterFill');
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Wizard::make([
                    Components\Wizard\Step::make('User')
                        ->icon('heroicon-s-user')
                        ->schema([
                            Components\Section::make('Account')
                                ->schema([
                                    $this->getEmailFormComponent(),
                                    $this->getPasswordFormComponent(),
                                    $this->getPasswordConfirmationFormComponent(),
                                ])
                                ->columnSpan(1),
                            Components\Section::make('Contact')
                                ->schema([
                                    $this->getNameFormComponent(),
                                    Components\Select::make('setting.iso3166_country_code')
                                        ->label('Country')
                                        ->options(fn () => $this->countries)
                                        ->default(fn () => array_key_first($this->countries))
                                        ->searchable()
                                        ->required(),
                                    PhoneInput::make('phone')
                                        ->label('Phone number')
                                        ->prefixIcon('heroicon-s-phone')
                                        ->defaultCountry(fn () => $this->ipLocation)
                                        ->onlyCountries(fn () => array_keys($this->countries))
                                        ->autoPlaceholder('aggressive')
                                        ->strictMode()
                                        ->required(),
                                ])
                                ->columnSpan(1),

                        ])
                        ->columns(),
                    Components\Wizard\Step::make('Business')
                        ->icon('heroicon-s-building-storefront')
                        ->schema([
                            Components\Section::make([
                                Components\TextInput::make('setting.company_name')
                                    ->label('Name')
                                    ->placeholder('JavaTechnovation Holdings.')
                                    ->required()
                                    ->maxLength(255),
                                Components\TextInput::make('setting.company_address')
                                    ->label('Address')
                                    ->placeholder('street/suburb, city, state')
                                    ->required()
                                    ->maxLength(255),
                            ])
                                ->columns(),
                            Components\Textarea::make('setting.company_about')
                                ->label('About')
                                ->placeholder('Your content goes in here...')
                                ->columnSpanFull(),
                        ])
                        ->columns(),
                ])
                    ->previousAction(fn (StaticAction $action) => $action->icon('heroicon-s-chevron-left'))
                    ->nextAction(fn (StaticAction $action) => $action->icon('heroicon-s-chevron-right'))
                    ->submitAction($this->getRegisterFormAction()->icon('heroicon-s-check-badge'))
                    ->skippable(),
            ]);
    }

    protected function handleRegistration(array $data): Model
    {
        $setting = $data['setting'];
        unset($data['setting']);

        $user = $this->getUserModel()::create($data);

        $user->setting()->create($setting);

        $user->role()->associate(Role::query()->find(Role::ADMIN))->save();

        return $user;
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make('throttled')
            ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]))
            ->body(array_key_exists('body', __('filament-panels::pages/auth/register.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/register.notifications.throttled.body', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]) : null)
            ->danger();
    }

    protected function afterRegister(): void
    {
        Notification::make('success')
            ->title('Registration success')
            ->success()
            ->send();
    }

    protected function sendEmailVerificationNotification(Model $user): void
    {
        if (! $user instanceof MustVerifyEmail) {
            return;
        }

        (new EmailVerificationPrompt)->sendEmailVerificationNotification($user);
    }

    /**
     * Fetch Paystack-supported countries.
     */
    protected function getPaystackSupportedCountries(): array
    {
        $countries = Cache::get('paystack-countries');

        if ($countries) {
            return $countries;
        }

        FetchPaystackCountries::dispatch();

        return ['NG' => 'Nigeria'];
    }

    /**
     * Fetch IP Location.
     */
    protected function getIpLocation(): string
    {
        $id = Session::id();
        $country = Cache::get("ip-location-{$id}");

        if ($country) {
            return $country;
        }

        FetchIpLocation::dispatch($id);

        return 'NG';
    }

    #[On('echo:updates,ApiFetched')]
    public function apiFetched($event)
    {
        if ($event['type'] === 'countries') {
            $this->countries = $event['data'];
            Notification::make('success')
                ->title('API Fetched')
                ->body('Country list updated.')
                ->success()
                ->send();
        } elseif ($event['type'] === 'ip-location') {
            $this->ipLocation = $event['data'];
            Notification::make('success')
                ->title('API Fetched')
                ->body('Location updated.')
                ->success()
                ->send();
        }

        $this->dispatch('refresh');
    }
}
