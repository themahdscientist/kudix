<?php

namespace App\Livewire;

use App\Models\User;
use Binkode\Paystack\Support\Plan;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;
use NumberFormatter;

#[Lazy]
#[Title('Pricing')]
class Pricing extends Component
{
    use WithRateLimiting;

    public array $plans;

    public string $interval;

    public array $filteredPlans;

    public array $selectedPlan;

    public ?string $recommendedPlan;

    public array $features;

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
        'growth' => [
            'Up to 3 user accounts',
            'Standard analytics & reporting',
            'Basic integrations',
            'Team collaboration tools',
            'Cloud data backup',
            'Access to mobile app',
        ],
        'starter' => [
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
        if (is_null(Cache::get('plans'))) {
            $res = rescue(fn () => Plan::list(), ['status' => false]);

            // Only save to cache if there are valid plans
            if ($res['status'] && ! empty($res['data'])) {
                Cache::put('plans', $res, now()->addMonths(3));
            }
        } else {
            $cachedPlans = Cache::get('plans');

            // Refresh cache if the current one is invalid or empty
            if (! $cachedPlans['status'] || empty($cachedPlans['data'])) {
                Cache::forget('plans');
                $res = rescue(fn () => Plan::list(), ['status' => false]);

                if ($res['status'] && ! empty($res['data'])) {
                    Cache::put('plans', $res, now()->addMonths(3));
                }
            } else {
                $res = $cachedPlans;
            }
        }

        if (isset($res) && $res['status'] && ! empty($res['data'])) {
            $this->plans = collect($res['data'])->groupBy('interval')->toArray() ?? null;

            $this->interval = 'monthly';

            // $this->selectedPlan = $this->plans[1] ?? null;

            // $this->recommendedPlan = $this->selectedPlan['name'] ?? 'No plan available';

            // $this->features = $this->selectedPlan
            //     ? $this->planFeatures[strtolower($this->selectedPlan['name'])] ?? []
            //     : [];

            return;
        }
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

    public function setInterval($interval)
    {
        $this->interval = $interval;
        $this->filteredPlans = $this->plans[$interval];
    }

    public function subscribe($plan)
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            \App\Utils::getRateLimitedNotification($exception)->send();

            return null;
        }

        if (! filament()->auth()->check()) {
            return $this->redirectRoute('filament.admin.auth.login');
        }

        if (! empty($plan)) {
            $user = filament()->auth()->user()->subToPlan($plan);

            if ($user instanceof User) {
                Notification::make('success')
                    ->title('Subscription success')
                    ->body(Str::markdown('**Payment verification complete**. Re-routing to dashboard.'))
                    ->success()
                    ->send();

                return $this->redirectIntended(route('filament.admin.pages.dashboard'), true);
            } else {
                Notification::make('error')
                    ->title('Offline')
                    ->body(Str::markdown('**Connect to the internet**, refresh the browser, and retry.'))
                    ->persistent()
                    ->icon('heroicon-s-signal-slash')
                    ->warning()
                    ->send();
            }
        }
    }
}
