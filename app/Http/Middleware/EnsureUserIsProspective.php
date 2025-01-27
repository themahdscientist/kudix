<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsProspective
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = filament()->auth()->user();

        if (! is_null($user) && $user->hasSubscription()) {
            // Check if the user is subscribed
            Notification::make('info')
                ->title('Navigation denied')
                ->body('You must be a prospective user to access that resource.')
                ->info()
                ->send();

            return redirect()->intended(filament()->getUrl());
        }

        return $next($request);
    }
}
