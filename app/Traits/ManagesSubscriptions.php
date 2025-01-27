<?php

namespace App\Traits;

use App\Models\User;
use Binkode\Paystack\Support\Subscription;
use Binkode\Paystack\Support\Transaction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait ManagesSubscriptions
{
    /**
     * Get the ending date of a trial.
     */
    public function trialEndsAt()
    {
        //
    }

    /**
     * Get all subscriptions for the user.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(\App\Models\Subscription::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the user's active subscription.
     */
    public function subscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->latest('created_at')
            ->first();
    }

    /**
     * Check if the user has an active subscription.
     */
    public function hasSubscription(): bool
    {
        return ! is_null($this->subscription());
    }

    public function getSubscription(string $subscription_code): ?array
    {
        $res = rescue(
            fn () => Subscription::fetch($subscription_code),
            ['status' => false],
        );

        if ($res['status'] && ! empty($res['data'])) {
            return $res['data'];
        }

        if (empty($res['data'])) {
            Notification::make('info')
                ->title('Subscription info')
                ->body('No such subscription on this integration.')
                ->persistent()
                ->info()
                ->send();

            return $res['data'];
        }

        Notification::make('error')
            ->title('Subscription error')
            ->body(Str::markdown('**Internet connectivity lost**.'))
            ->persistent()
            ->danger()
            ->send();

        return null;
    }

    public function createSubscription(string $plan_code)
    {
        $this->createAsPaystackCustomer();

        $options = [
            'customer' => $this->customer_code,
            'plan' => $plan_code,
            'authorization' => $this->getAuth(),
            'start_date' => now(),
        ];

        $res = rescue(
            fn () => Subscription::create($options),
            ['status' => false],
        );

        if ($res['status']) {
            return $res['data'];
        }

        Notification::make('error')
            ->title('Subscription error')
            ->body(Str::markdown('**Internet connectivity lost**.'))
            ->persistent()
            ->danger()
            ->send();

        return $res['status'];
    }

    public function persistSubscription(string $subscription_code, array $options = []): User
    {
        $data = $this->getSubscription($subscription_code);

        if (! empty($data) && ! is_null($data)) {
            $this->subscriptions()->updateOrCreate([
                'subscription_code' => $data['subscription_code'],
            ], [
                'amount' => $data['amount'],
                'ends_at' => isset($data['next_payment_date']) ? now()->parse($data['next_payment_date'])->format('Y-m-d H:i:s') : null,
                'plan_code' => $data['plan']['plan_code'],
                'quantity' =>  $options['quantity'] ?? $data['quantity'],
                'starts_at' => now()->parse($options['starts_at'] ?? $data['createdAt'])->format('Y-m-d H:i:s'),
                'status' => $data['status'],
                'trial_ends_at' => now(),
                'type' => $data['plan']['name'],
            ]);

            return $this;
        }
    }

    public function startTrial(int $days): void
    {
        $this->subscription()->update(['trial_ends_at' => now()->addDays($days)]);
    }

    public function initializeTransactionForSubscription($plan_code)
    {
        if ($this->isAuthorized()) {
            return null;
        }

        $options = [
            'email' => $this->paystackEmail(),
            'amount' => 50 * 100,
            'plan' => $plan_code,
            'callback_url' => route('paystack.callback'),
            'metadata' => [
                'cancel_action' => route('pricing'),
                'custom_filters' => [
                    'recurring' => true,
                ],
            ],
            'channels' => ['card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer', 'eft'],
        ];

        return rescue(
            fn () => Transaction::initialize($options),
            ['status' => false],
        );
    }

    /**
     * Subscribe the user to a plan.
     */
    public function subToPlan(string $plan_code, array $options = []): User|\Illuminate\Routing\Redirector|Notification
    {
        // Ensure customer exists
        $this->createAsPaystackCustomer();

        $trx = $this->initializeTransactionForSubscription($plan_code);

        if (! is_null($trx)) {
            if ($trx['status']) {
                Notification::make('success')
                    ->title('Subscription success')
                    ->body(Str::markdown('**Payment authorization complete**. Re-routing to checkout.'))
                    ->success()
                    ->send();

                return redirect($trx['data']['authorization_url']);
            }

            return Notification::make('error')
                ->title('Subscription error')
                ->body(Str::markdown('**Internet connectivity lost**. Refresh the browser and retry.'))
                ->persistent()
                ->danger()
                ->send();
        }

        $sub = $this->createSubscription($plan_code);

        // if ($sub !== false) {
        //     return $this->persistSubscription($sub['subscription_code']);
        // }
        
        dd($sub);
        return Notification::make('error')
            ->title('Subscription error')
            ->body(Str::markdown('**Internet connectivity lost**.'))
            ->persistent()
            ->danger()
            ->send();
    }

    /**
     * Cancel the user's subscription.
     */
    public function cancelSubscription(): void
    {
        $subscription = $this->subscription();

        if (! $subscription) {
            throw new \Exception('No active subscription to cancel.');
        }

        $response = Subscription::disable([
            'code' => $subscription->subscription_code,
            'token' => $subscription->plan_code,
        ]);

        if (! $response['status']) {
            Log::error('Failed to cancel subscription on Paystack: '.$response['message']);
            throw new \Exception('Failed to cancel subscription.');
        }

        $subscription_details = $this->getSubscription($subscription->subscription_code);

        $subscription->update([
            'status' => 'cancelled',
            'ends_at' => $subscription_details['data']['next_payment_date'],
        ]);
    }
}
