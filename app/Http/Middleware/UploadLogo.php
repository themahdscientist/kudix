<?php

namespace App\Http\Middleware;

use App\Filament\Admin\Pages\UploadLogo as UploadLogoPage;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadLogo
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! filament()->auth()->check()) {
            return $next($request);
        }

        if (filament()->auth()->user()->setting->company_logo == null) {
            return redirect();
        }

        return $next($request);
    }
}
