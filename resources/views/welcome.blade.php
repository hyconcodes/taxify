<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('Welcome') }} - {{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 text-white antialiased">
        <div class="relative flex min-h-screen flex-col">
            <div class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-amber-500/10 via-transparent to-transparent"></div>

            <header class="relative z-10 flex w-full items-center justify-between px-6 py-5 lg:px-10">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500 shadow-lg shadow-amber-500/25">
                        <svg class="h-6 w-6 text-slate-950" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight">{{ config('app.name', 'Taxify') }}</span>
                </div>

                @if (Route::has('login'))
                    <nav class="flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 shadow-lg shadow-amber-500/20">
                                {{ __('Dashboard') }}
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                                {{ __('Log in') }}
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 shadow-lg shadow-amber-500/20">
                                    {{ __('Register') }}
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </header>

            <main class="relative z-10 mx-auto flex w-full max-w-6xl flex-1 flex-col items-center justify-center px-6 py-12 lg:px-10">
                <div class="mb-16 text-center">
                    <span class="inline-block rounded-full border border-amber-500/30 bg-amber-500/10 px-4 py-1.5 text-xs font-medium tracking-wider text-amber-400 uppercase">
                        {{ __('Vehicle Plate Recognition System') }}
                    </span>
                    <h1 class="mt-6 text-4xl font-bold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                        {{ __('Intelligent Plate') }}
                        <span class="bg-gradient-to-r from-amber-400 to-amber-300 bg-clip-text text-transparent">{{ __('Recognition') }}</span>
                        <br />
                        {{ __('for Crime Control') }}
                    </h1>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-400">
                        {{ __('Register vehicle plates and owner profiles, capture plates via OCR or camera, and get instant alerts on unmatched plates.') }}
                    </p>
                    @guest
                        <div class="mt-8 flex items-center justify-center gap-4">
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-6 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 shadow-lg shadow-amber-500/20">
                                {{ __('Get Started') }}
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                            <a href="#features" class="rounded-lg px-6 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/10">
                                {{ __('Learn More') }}
                            </a>
                        </div>
                    @endguest
                </div>

                <div id="features" class="grid w-full max-w-4xl gap-6 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-700/50 bg-slate-800/40 p-8 backdrop-blur-sm transition hover:border-amber-500/30">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/20">
                            <svg class="h-6 w-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold">{{ __('Register Vehicles') }}</h3>
                        <p class="text-sm text-slate-400">{{ __('Store plate numbers, vehicle details, and owner profiles in a centralized database for quick access and verification.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-700/50 bg-slate-800/40 p-8 backdrop-blur-sm transition hover:border-amber-500/30">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/20">
                            <svg class="h-6 w-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Zm16.5-12.75a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0Z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold">{{ __('Capture & Recognize') }}</h3>
                        <p class="text-sm text-slate-400">{{ __('Use camera capture or image upload with OCR to automatically read and recognize license plate numbers.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-700/50 bg-slate-800/40 p-8 backdrop-blur-sm transition hover:border-amber-500/30">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/20">
                            <svg class="h-6 w-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold">{{ __('Match & Alert') }}</h3>
                        <p class="text-sm text-slate-400">{{ __('Automatically match captured plates against the registered database and generate alerts for unrecognized vehicles.') }}</p>
                    </div>
                </div>

                <div class="mt-12 grid w-full max-w-4xl grid-cols-3 gap-6">
                    <div class="rounded-xl border border-slate-700/50 bg-slate-800/30 p-6 text-center backdrop-blur-sm">
                        <p class="text-3xl font-bold text-amber-400">{{ $stats['vehicles'] ?? 0 }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Registered Vehicles') }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-700/50 bg-slate-800/30 p-6 text-center backdrop-blur-sm">
                        <p class="text-3xl font-bold text-amber-400">{{ $stats['captures'] ?? 0 }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Plate Captures') }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-700/50 bg-slate-800/30 p-6 text-center backdrop-blur-sm">
                        <p class="text-3xl font-bold text-amber-400">{{ $stats['alerts'] ?? 0 }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Active Alerts') }}</p>
                    </div>
                </div>
            </main>

            <footer class="relative z-10 border-t border-slate-800/50 px-6 py-5">
                <p class="text-center text-xs text-slate-600">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Taxify') }}. {{ __('All rights reserved.') }}
                </p>
            </footer>
        </div>

        @fluxScripts
    </body>
</html>
