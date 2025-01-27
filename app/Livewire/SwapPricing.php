<?php

namespace App\Livewire;

use NumberFormatter;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Binkode\Paystack\Support\Plan;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Support\Facades\Cache;
use Filament\Notifications\Notification;

#[Lazy]
#[Layout('components.layouts.billing')]
#[Title('Change subscription')]
class SwapPricing extends Component
{
    use WithRateLimiting;

    public array $plans;

    public array $currentPlan;

    public array $selectedPlan;

    public array $features;

    public $user;

    protected $planFeatures = [
        'enterprise' => [
            'Unlimited user accounts',
            'Priority 24/7 support',
            'Advanced analytics & reporting',
            'Custom integrations',
            'Dedicated account manager',
            'Unlimited API access',
            'Customizable dashboards',
            'Multi-branch management',
            'Enterprise-grade security',
            '99.99% uptime SLA',
        ],
        'business' => [
            'Up to 10 user accounts',
            'Premium support',
            'Detailed analytics & reporting',
            'Third-party integrations',
            'API access',
            'Custom branding',
            'Role-based access controls',
            'Regular system updates',
        ],
        'professional' => [
            'Up to 3 user accounts',
            'Standard analytics & reporting',
            'Basic integrations',
            'Team collaboration tools',
            'Cloud data backup',
            'Access to mobile app',
        ],
        'standard' => [
            'Only one core account',
            'Basic analytics',
            'Access to core features',
            'Standard security protocols',
            'Limited API access',
            'Weekly backups',
        ],
    ];

    public function mount()
    {
        $this->user = filament()->auth()->user()->load('subscriptions');

        if (is_null(Cache::get('plans'))) {
            Cache::put('plans', rescue(fn () => Plan::list(), ['status' => false]), now()->addMonths(3));
            $res = Cache::get('plans');
        } elseif (! Cache::get('plans')['status']) {
            Cache::forget('plans');
            Cache::put('plans', rescue(fn () => Plan::list(), ['status' => false]), now()->addMonths(3));
            $res = Cache::get('plans');
        } else {
            $res = Cache::get('plans');
        }

        if ($res['status']) {
            $this->plans = array_reverse($res['data']);

            $this->currentPlan = collect($this->plans)->reject(function ($plan) {
                return $plan['plan_code'] !== $this->user->subscription()->plan_code;
            })->first();

            $this->selectedPlan = $this->currentPlan;

            $this->features = $this->planFeatures[strtolower($this->selectedPlan['name'])];

            return;
        }

        $this->plans = [];
    }

    public function fmt($val)
    {
        return (new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY))->formatCurrency(round(abs($val) / 100), 'NGN');
    }

    public function selectPlan($index)
    {
        $this->selectedPlan = $this->plans[$index];
        $this->features = $this->planFeatures[strtolower($this->selectedPlan['name'])] ?? [];
    }

    public function switch($plan)
    {
        try {
            $this->rateLimit(5, now()->addHour());
        } catch (TooManyRequestsException $exception) {
            \App\Utils::getRateLimitedNotification($exception)?->send();

            return null;
        }

        if (! filament()->auth()->check()) {
            return $this->redirectRoute('filament.admin.auth.login');
        }

        if (! empty($plan)) {
            // filament()->auth()->user()->subToPlan($plan);

            return;
        }

        Notification::make('error')
            ->title('Offline')
            ->body(Str::markdown('**Connect to the internet**, refresh the browser, and retry.'))
            ->persistent()
            ->icon('heroicon-s-signal-slash')
            ->warning()
            ->send();
    }
}