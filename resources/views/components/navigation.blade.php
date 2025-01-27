<nav
    class="flex items-center justify-between px-20 py-4 border-b-2 bg-accent dark:bg-primary border-b-dark dark:border-b-light">
    <section class="flex items-center gap-8 text-sm">
        <div class="w-10 lg:w-12">
            <div class="dark:hidden">
                <img src="{{ asset('images/logo-dark.svg') }}" alt="{{ config('app.name') }} logo">
            </div>
            <div class="hidden dark:block">
                <img src="{{ asset('images/logo-light.svg') }}" alt="{{ config('app.name') }} logo">
            </div>
        </div>
        <ul class="flex items-center gap-6 p-4 rounded-xl">
            <li>
                <a wire:navigate href="{{ route('welcome') }}"
                    class="pb-1 transition border-b-2 dark:hover:border-b-light hover:border-b-dark @if(request()->routeIs('welcome')) dark:border-b-light border-b-dark @else border-b-transparent @endif">Home</a>
            </li>
            <li>
                <a wire:navigate href="{{ route('pricing') }}"
                    class="pb-1 transition border-b-2 dark:hover:border-b-light hover:border-b-dark @if(request()->routeIs('pricing')) dark:border-b-light border-b-dark @else border-b-transparent @endif">Pricing</a>
            </li>
        </ul>
    </section>
    <section class="flex items-center gap-8 text-sm">
        @auth
        <div>
            <a href="{{ route('filament.'.filament()->getId().'.pages.dashboard') }}"
                class="px-4 py-2 font-bold transition rounded-lg hover:scale-105 focus:scale-95 bg-dark dark:bg-light text-light dark:text-dark">Dashboard</a>
        </div>
        @else
        <div class="flex items-center gap-4 text-dark">
            <a href="{{ route('filament.admin.auth.login') }}"
                class="px-4 py-2 font-bold transition rounded-lg hover:scale-105 focus:scale-95 bg-light dark:bg-dark text-dark dark:text-light">Login</a>
            <a href="{{ route('filament.admin.auth.register') }}"
                class="px-4 py-2 font-bold transition rounded-lg hover:scale-105 focus:scale-95 bg-dark dark:bg-light text-light dark:text-dark">Register</a>
        </div>
        @endauth
        <x-filament-panels::theme-switcher.index />
    </section>
</nav>