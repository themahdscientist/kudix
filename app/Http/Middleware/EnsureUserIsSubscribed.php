<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSubscribed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Add routes to exclude from the subscription check
        $excludedRoutes = [
            'filament.admin.auth.logout', // Add the logout route name
            'filament.cashier.auth.logout', // Add the logout route name
        ];

        if (in_array(Route::currentRouteName(), $excludedRoutes)) {
            return $next($request);
        }

        $user = filament()->auth()->user();

        if (isset($user->user_id)) {
            if (! User::query()->find($user->user_id)->subscribed()) {
                Notification::make('info')
                    ->title('Navigation denied')
                    ->body('You must have a subscribed administrator to access that resource.')
                    ->info()
                    ->send();

                return redirect()->route('pricing');
            }

            return $next($request);
        }

        // Check if the user is subscribed
        if (! $user->subscribed()) {
            Notification::make('info')
                ->title('Navigation denied')
                ->body('You must be a subscribed user to access that resource.')
                ->info()
                ->send();

            return redirect()->route('pricing');
        }

        return $next($request);
    }
}
