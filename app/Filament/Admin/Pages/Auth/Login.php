<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Forms\Components;
use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    protected function getRememberFormComponent(): Components\Component
    {
        return Components\Grid::make()
            ->schema([
                Components\Checkbox::make('remember')
                    ->label(__('filament-panels::pages/auth/login.form.remember.label')),
                Components\Actions::make([
                    Components\Actions\Action::make('Cashier?')
                        ->link()
                        ->url(filament()->getPanel('cashier')->getLoginUrl()),
                ])
                    ->alignEnd()
                    ->verticallyAlignCenter(),
            ]);
    }
}
