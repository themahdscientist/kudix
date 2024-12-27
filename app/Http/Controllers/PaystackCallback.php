<?php

namespace App\Http\Controllers;

use Binkode\Paystack\Support\Plan;
use Binkode\Paystack\Support\Transaction;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaystackCallback extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (! filament()->auth()->check()) {
            return;
        }

        $res = rescue(
            fn () => Transaction::verify($request->reference),
            ['status' => false],
        );

        if ($res['status'] && $res['data']['status'] === 'success') {
            $user = $request->user();

            $user->update([
                'auth' => $res['data']['authorization'],
            ]);

            $subscriptions = collect(
                rescue(
                    fn () => Plan::fetch($res['data']['plan']),
                    ['status' => false],
                )['data']['subscriptions'] ?? []
            );

            $subscription = $subscriptions
                ->filter(
                    function ($sub) use ($user) {
                        return
                        $sub['authorization']['authorization_code'] === $user->auth['authorization_code'] &&
                        $sub['customer']['customer_code'] === $user->customer_code;
                    })
                ->sortByDesc('createdAt')
                ->first();

            if ($subscription) {
                $user->persistSubscription($subscription['subscription_code'], $subscription['start']);

                Notification::make('success')
                    ->title('Subscription success')
                    ->body(Str::markdown('**Payment verification complete**. Re-routing to dashboard.'))
                    ->success()
                    ->send();

                return redirect()->route('filament.admin.pages.dashboard');
            }

            Notification::make('error')
                ->title('Subscription error')
                ->body(Str::markdown('**Failed to retrieve subscription**. Re-routing to pricing.'))
                ->danger()
                ->send();

            return redirect()->route('pricing');

        }

        Notification::make('error')
            ->title('Subscription error')
            ->body(Str::markdown('**Payment verification failed**. Re-routing to pricing.'))
            ->danger()
            ->send();

        return redirect()->route('pricing');
    }
}
