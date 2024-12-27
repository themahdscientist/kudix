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
        [x-cloak] {
            display: none !important;
        }
    </style>
    
    @filamentStyles
    @vite('resources/css/app.css')
    
    <!-- Include your assets -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@100;200;300;400;500;600;700&amp;display=swap"
        rel="stylesheet">
</head>

<body class="relative dark:bg-dark bg-light text-dark dark:text-light font-[Parkinsans] antialiased">
    <header class="sticky top-0 left-0">
        <nav class="flex items-center justify-around p-4 bg-secondary">
            <section class="w-10 lg:w-12">
                <div class="dark:hidden">
                    <img src="{{asset('images/logo-dark.svg')}}" alt="{{config('app.name')}} logo">
                </div>
                <div class="hidden dark:block">
                    <img src="{{asset('images/logo-light.svg')}}" alt="{{config('app.name')}} logo">
                </div>
            </section>
            <ul class="flex items-center gap-4 p-4 rounded-xl">
                <li>
                    <a href="{{route('index')}}"
                        class="p-1 transition border-b-2 dark:hover:border-b-primary hover:border-b-accent @if(request()->routeIs('index')) dark:border-b-primary border-b-accent @else border-b-transparent @endif">Home</a>
                </li>
                <li>
                    <a href="{{route('pricing')}}"
                        class="p-1 transition border-b-2 dark:hover:border-b-primary hover:border-b-accent @if(request()->routeIs('pricing')) dark:border-b-primary border-b-accent @else border-b-transparent @endif">Pricing</a>
                </li>
            </ul>
            <section class="flex items-center">
                @auth
                <div>
                    <a href="{{filament()->getId(). '/dashboard'}}"
                        class="px-4 py-2 font-bold transition duration-300 rounded-full bg-accent dark:bg-primary dark:text-light">Dashboard</a>
                </div>
                @else
                <div class="flex items-center gap-4 text-dark">
                    <a href="{{route('filament.admin.auth.login')}}"
                        class="px-4 py-2 font-bold transition duration-300 border-2 rounded-full border-accent dark:border-primary dark:text-light">Login</a>
                    <a href="{{route('filament.admin.auth.register')}}"
                        class="px-4 py-2 font-bold transition duration-300 rounded-full bg-accent dark:bg-primary dark:text-light">Register</a>
                </div>
                @endauth
            </section>
        </nav>
    </header>
    <main>
        {{ $slot }}
    </main>
    @livewire('notifications')

    @filamentScripts
    @vite('resources/js/app.js')
</body>

</html>