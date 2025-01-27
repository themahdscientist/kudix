<?php

namespace App\Filament\Admin\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
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

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]))
            ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]) : null)
            ->danger();
    }
}
