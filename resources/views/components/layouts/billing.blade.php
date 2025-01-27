@props([
'livewire' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ __('filament-panels::layout.direction') ?? 'ltr' }}" @class([ 'fi min-h-screen' , 'dark'=>
filament()->hasDarkModeForced(),
])
>

<head>
    <meta charset="utf-8" />

    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    @if ($favicon = filament()->getFavicon())
    <link rel="icon" href="{{ $favicon }}" />
    @endif

    @php
    $title = $title ?? trim(strip_tags(($livewire ?? null)?->getTitle() ?? ''));
    $brandName = trim(strip_tags(filament()->getBrandName()));
    @endphp

    <title>
        {{ filled($title) ? "{$title} - " : null }} {{ $brandName }}
    </title>


    <style>
        [x-cloak=''],
        [x-cloak='x-cloak'],
        [x-cloak='1'] {
            display: none !important;
        }

        @media (max-width: 1023px) {
            [x-cloak='-lg'] {
                display: none !important;
            }
        }

        @media (min-width: 1024px) {
            [x-cloak='lg'] {
                display: none !important;
            }
        }
    </style>

    @filamentStyles

    {{ filament()->getTheme()->getHtml() }}
    {{ filament()->getFontHtml() }}

    <style>
        :root {
            --font-family: '{!! filament()->getFontFamily() !!}';
            --default-theme-mode: '{{ filament()->getDefaultThemeMode()->value }}';
        }
    </style>

    @stack('styles')

    @if (! filament()->hasDarkMode())
    <script>
        localStorage.setItem('theme', 'light')
    </script>
    @elseif (filament()->hasDarkModeForced())
    <script>
        localStorage.setItem('theme', 'dark')
    </script>
    @else
    <script>
        const theme = localStorage.getItem('theme') ?? @js(filament()->getDefaultThemeMode()->value)

                if (
                    theme === 'dark' ||
                    (theme === 'system' &&
                        window.matchMedia('(prefers-color-scheme: dark)')
                            .matches)
                ) {
                    document.documentElement.classList.add('dark')
                }
    </script>
    @endif

    @vite('resources/css/app.css')
</head>

<body class="dark:bg-dark bg-light text-dark dark:text-light font-[Parkinsans] antialiased">
    <div class="flex flex-col items-center lg:flex-row">
        <aside
            class="flex flex-col justify-between h-screen sticky start-0 inset-y-0 lg:p-16 dark:bg-primary bg-accent flex-[.625]">
            <section>
                <a href="{{ route('filament.admin.pages.dashboard') }}" class="block w-8 md:w-12">
                    <div class="dark:hidden">
                        <img src="{{ asset('images/logo-dark.svg') }}" alt="{{ config('app.name') }} logo">
                    </div>
                    <div class="hidden dark:block">
                        <img src="{{ asset('images/logo-light.svg') }}" alt="{{ config('app.name') }} logo">
                    </div>
                </a>
                <p class="mt-24 text-xl font-bold lg:text-3xl">
                    Simplify your subscriptions.<br />Empower your choices.
                </p>
                <a href="{{ route('filament.admin.pages.dashboard') }}"
                    class="flex items-center gap-2 mt-8 text-sm transition duration-300 hover:opacity-75">
                    @svg('heroicon-s-arrow-left-end-on-rectangle', 'h-4 w-4')
                    Return to Kudix
                </a>
            </section>
            <section class="flex flex-col items-start gap-2 text-sm">
                <div>
                    <a x-tooltip="'Paystack'" href="https://paystack.com"
                        class="items-center hidden gap-2 transition duration-300 dark:flex hover:opacity-75">
                        Powered by <img
                            src="https://cdn.brandfetch.io/paystack.com/w/80/h/14/theme/light/logo?c={{ env('BRANDFETCH_CLIENT_ID') }}"
                            alt="Logos by Brandfetch" />
                    </a>
                    <a x-tooltip="'Paystack'" href="https://paystack.com"
                        class="flex items-center gap-2 transition duration-300 dark:hidden hover:opacity-75">
                        Powered by <img
                            src="https://cdn.brandfetch.io/paystack.com/w/80/h/14/theme/dark/logo?c={{ env('BRANDFETCH_CLIENT_ID') }}"
                            alt="Logos by Brandfetch" />
                    </a>
                </div>
                <a href="https://support.paystack.com/" class="transition duration-300 hover:opacity-75">Learn more
                    about
                    Paystack Billing</a>
            </section>
        </aside>
        <main class="flex-1 lg:p-20">
        {{ $slot }}
        </main>
    </div>
    @livewire(Filament\Livewire\Notifications::class)


    @filamentScripts(withCore: true)

    @if (filament()->hasBroadcasting() && config('filament.broadcasting.echo'))
    <script data-navigate-once>
        window.Echo = new window.EchoFactory(@js(config('filament.broadcasting.echo')))

                window.dispatchEvent(new CustomEvent('EchoLoaded'))
    </script>
    @endif

    @vite('resources/js/app.js')

    @stack('scripts')
</body>

</html>