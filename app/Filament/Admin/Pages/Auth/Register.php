<?php

namespace App\Filament\Admin\Pages\Auth;

use App\Forms\Components as AppComponents;
use App\Models\Role;
use Filament\Actions\StaticAction;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class Register extends BaseRegister
{
    protected ?string $maxWidth = '4xl';

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
                        ->description('Personal details')
                        ->schema([
                            Components\Section::make('Account')
                                ->description('Login details.')
                                ->schema([
                                    $this->getNameFormComponent(),
                                    $this->getPasswordFormComponent(),
                                    $this->getPasswordConfirmationFormComponent(),
                                ])
                                ->columnSpan(1),
                            Components\Section::make('Contact')
                                ->description('Communication details.')
                                ->schema([
                                    $this->getEmailFormComponent(),
                                    AppComponents\LocalizedCountrySelect::make('country'),
                                    PhoneInput::make('phone')
                                        ->label('Phone number')
                                        ->prefixIcon('heroicon-s-phone')
                                        ->defaultCountry('NG')
                                        ->autoPlaceholder('aggressive')
                                        ->ipLookup(function () {
                                            return rescue(
                                                fn () => Http::get('https://ipinfo.io', ['token' => env('IPINFO_SECRET')])->json('country'),
                                                'NG',
                                                false
                                            );
                                        })
                                        ->strictMode()
                                        ->required(),
                                ])
                                ->columnSpan(1),

                        ])
                        ->columns(),
                    Components\Wizard\Step::make('Company')
                        ->icon('heroicon-s-building-office')
                        ->description('Pharmacy details.')
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

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['is_subscribed'] = false;

        return $data;
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

    protected function sendEmailVerificationNotification(Model $user): void
    {
        if (! $user instanceof MustVerifyEmail) {
            return;
        }

        (new EmailVerificationPrompt)->sendEmailVerificationNotification($user);
    }
}
