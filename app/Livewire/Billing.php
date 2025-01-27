<?php

namespace App\Livewire;

use Binkode\Paystack\Support\Subscription;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use NumberFormatter;

#[Layout('components.layouts.billing')]
#[Title('Billing')]
class Billing extends Component
{
    public array $auth;

    public $subscription;

    public $user;

    public function mount()
    {
        $this->user = filament()->auth()->user();

        $this->subscription = $this->user->subscription();

        $this->auth = $this->user->auth->toArray();
    }

    public function manageCardsOrCancelSubscription()
    {
        $res = rescue(
            fn () => Subscription::link($this->subscription->subscription_code),
            ['status' => false]
        );

        if ($res['status']) {
            $this->dispatch('link-generated', $res['data']['link']);

            return;
        }

        return Notification::make('error')
            ->title('Offline')
            ->body(Str::markdown('**Connect to the internet** and retry.'))
            ->warning()
            ->send();
    }

    public function fmt($val)
    {
        return (new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY))->formatCurrency(round(abs($val) / 100), 'NGN');
    }
}
