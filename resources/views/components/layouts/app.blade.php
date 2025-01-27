<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="shortcut icon" href="{{asset('favicon.svg')}}" type="image/svg">

    <title>{{ $title ?? 'Page Title' }} - {{ config('app.name') }}</title>

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

<body class="relative dark:bg-dark bg-light text-dark dark:text-light font-[Parkinsans] antialiased">
    <header class="sticky top-0 left-0 z-50">
        <x-navigation />
    </header>
    <main>
        {{ $slot }}
    </main>
    
    @livewire('notifications')

    @filamentScripts

    @vite('resources/js/app.js')
    
    @stack('scripts')
</body>

</html>