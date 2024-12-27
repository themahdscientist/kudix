<?php

namespace App\Http\Middleware;

use Closure;
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
        $user = $request->user();
        // Add routes to exclude from the subscription check
        $excludedRoutes = [
            'filament.admin.auth.logout', // Add the logout route name
        ];

        if (in_array(Route::currentRouteName(), $excludedRoutes)) {
            return $next($request);
        }

        // Check if the user is subscribed
        if (! $user->hasSubscription()) {
            return redirect()->route('pricing');
        }

        return $next($request);
    }
}
