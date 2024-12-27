<?php

namespace App\Livewire;

use Binkode\Paystack\Support\Plan;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use NumberFormatter;

#[Title('Pricing')]
class Pricing extends Component
{
    public array $res;

    public array $plans;

    public $selectedPlan;

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
        $this->res = rescue(fn () => Plan::list(), ['status' => false]);

        if ($this->res['status']) {
            $this->plans = array_reverse($this->res['data']);

            $this->selectedPlan = $this->plans[0] ?? null;
            $this->features = $this->planFeatures[strtolower($this->selectedPlan['name'])] ?? [];
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

    public function subscribe($plan)
    {
        if (! filament()->auth()->check()) {
            return $this->redirectRoute('filament.admin.auth.login');
        }

        if (! empty($plan)) {
            filament()->auth()->user()->subToPlan($plan);

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
