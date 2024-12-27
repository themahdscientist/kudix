<?php

namespace App\Traits;

trait Billable
{
    use ManagesCustomer, ManagesSubscriptions;

    /**
     * Start a generic trial for the user.
     */
    public function startGenericTrial(int $days): void
    {
        $this->update(['trial_ends_at' => now()->addDays($days)]);
    }

    /**
     * Check if the user is on a trial period.
     */
    public function onGenericTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Determine if the user is on a grace period after subscription cancellation.
     */
    public function onGracePeriod(): bool
    {
        $subscription = $this->subscriptions()
            ->where('status', 'cancelled')
            ->latest('ends_at')
            ->first();

        return $subscription && $subscription->ends_at && $subscription->ends_at->isFuture();
    }

    /**
     * Determine if the user has an active subscription or is on trial.
     */
    public function subscribed(): bool
    {
        return $this->hasSubscription() || $this->onGenericTrial();
    }
}
