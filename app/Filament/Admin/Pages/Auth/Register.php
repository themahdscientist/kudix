<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Hidden::make('is_admin')
                    ->default(true)
                    ->required(),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                \Filament\Forms\Components\TextInput::make('password')
                    ->label(__('filament-panels::pages/auth/register.form.password.label'))
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->required()
                    ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state))
                    ->same('passwordConfirmation')
                    ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute')),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
