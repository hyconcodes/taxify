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
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 text-slate-900 antialiased dark:from-slate-950 dark:via-blue-950 dark:to-slate-900 dark:text-white">
        <div class="relative flex min-h-screen flex-col">
            <div class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-amber-500/10 via-transparent to-transparent"></div>

            <header class="relative z-10 flex w-full items-center justify-between px-6 py-5 lg:px-10">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500 p-2 shadow-lg shadow-amber-500/25">
                        <x-app-logo-icon class="h-full w-full stroke-current text-slate-950" />
                    </div>
                    <span class="text-xl font-bold tracking-tight">{{ config('app.name', 'Taxify') }}</span>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Theme Toggle --}}
                    <button
                        x-data
                        @click="$flux.appearance = $flux.appearance === 'dark' ? 'light' : 'dark'"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                        title="{{ __('Toggle theme') }}"
                    >
                        <svg x-show="$flux.appearance !== 'dark'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                        </svg>
                        <svg x-show="$flux.appearance === 'dark'" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                    </button>

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
                                <a href="{{ route('login') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200/50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
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
                </div>
            </header>

            <main class="relative z-10 mx-auto flex w-full max-w-6xl flex-1 flex-col items-center justify-center px-6 py-12 lg:px-10">
                <div class="mb-16 text-center">
                    <span class="inline-block rounded-full border border-amber-500/30 bg-amber-500/10 px-4 py-1.5 text-xs font-medium tracking-wider text-amber-600 uppercase dark:text-amber-400">
                        {{ __('Vehicle Plate Recognition System') }}
                    </span>
                    <h1 class="mt-6 text-4xl font-bold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                        {{ __('Intelligent Plate') }}
                        <span class="bg-gradient-to-r from-amber-500 to-amber-400 bg-clip-text text-transparent dark:from-amber-400 dark:to-amber-300">{{ __('Recognition') }}</span>
                        <br />
                        {{ __('for Crime Control') }}
                    </h1>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-500 dark:text-slate-400">
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
                            <a href="#features" class="rounded-lg px-6 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-200/50 dark:text-slate-300 dark:hover:bg-white/10">
                                {{ __('Learn More') }}
                            </a>
                        </div>
                    @endguest
                </div>

                <div id="features" class="grid w-full max-w-4xl gap-6 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white/60 p-8 backdrop-blur-sm transition hover:border-amber-500/30 dark:border-slate-700/50 dark:bg-slate-800/40">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/20">
                            <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold">{{ __('Register Vehicles') }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Store plate numbers, vehicle details, and owner profiles in a centralized database for quick access and verification.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white/60 p-8 backdrop-blur-sm transition hover:border-amber-500/30 dark:border-slate-700/50 dark:bg-slate-800/40">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/20">
                            <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Zm16.5-12.75a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0Z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold">{{ __('Capture & Recognize') }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Use camera capture or image upload with OCR to automatically read and recognize license plate numbers.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white/60 p-8 backdrop-blur-sm transition hover:border-amber-500/30 dark:border-slate-700/50 dark:bg-slate-800/40">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/20">
                            <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold">{{ __('Match & Alert') }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Automatically match captured plates against the registered database and generate alerts for unrecognized vehicles.') }}</p>
                    </div>
                </div>

                <div class="mt-16 w-full max-w-5xl">
                    <div class="mb-10 text-center">
                        <span class="inline-block rounded-full border border-amber-500/30 bg-amber-500/10 px-4 py-1.5 text-xs font-medium tracking-wider text-amber-600 uppercase dark:text-amber-400">
                            {{ __('System Workflow') }}
                        </span>
                        <h2 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">
                            {{ __('How Taxify') }}
                            <span class="bg-gradient-to-r from-amber-500 to-amber-400 bg-clip-text text-transparent dark:from-amber-400 dark:to-amber-300">{{ __('Works') }}</span>
                        </h2>
                    </div>

                    <div class="relative grid gap-8 lg:grid-cols-3">
                        <div class="relative flex flex-col items-center text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-100 to-amber-50 ring-1 ring-amber-200 dark:from-amber-500/20 dark:to-amber-500/5 dark:ring-amber-500/30">
                                <svg class="h-8 w-8 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <span class="mt-2 flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-primary-950">1</span>
                            <h3 class="mt-3 text-lg font-semibold">{{ __('Register Vehicles') }}</h3>
                            <p class="mt-2 text-sm text-slate-500 leading-relaxed dark:text-slate-400">
                                {{ __('Store plate numbers, vehicle specs, and owner profiles in a secure centralized database for instant access.') }}
                            </p>
                        </div>

                        <div class="relative flex flex-col items-center text-center">
                            <div class="absolute top-8 left-0 right-0 hidden lg:block">
                                <div class="mx-auto h-px w-3/4 bg-gradient-to-r from-transparent via-amber-500/40 to-transparent"></div>
                            </div>
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-100 to-amber-50 ring-1 ring-amber-200 dark:from-amber-500/20 dark:to-amber-500/5 dark:ring-amber-500/30">
                                <svg class="h-8 w-8 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Zm16.5-12.75a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0Z" />
                                </svg>
                            </div>
                            <span class="mt-2 flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-primary-950">2</span>
                            <h3 class="mt-3 text-lg font-semibold">{{ __('Capture & Recognize') }}</h3>
                            <p class="mt-2 text-sm text-slate-500 leading-relaxed dark:text-slate-400">
                                {{ __('Use camera or image upload with OCR technology to automatically read and recognize license plates in real time.') }}
                            </p>
                        </div>

                        <div class="relative flex flex-col items-center text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-100 to-amber-50 ring-1 ring-amber-200 dark:from-amber-500/20 dark:to-amber-500/5 dark:ring-amber-500/30">
                                <svg class="h-8 w-8 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                </svg>
                            </div>
                            <span class="mt-2 flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-primary-950">3</span>
                            <h3 class="mt-3 text-lg font-semibold">{{ __('Match & Alert') }}</h3>
                            <p class="mt-2 text-sm text-slate-500 leading-relaxed dark:text-slate-400">
                                {{ __('Instantly cross-reference plates against registered vehicles and trigger alerts for unrecognized or suspicious plates.') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-12 rounded-2xl border border-slate-200 bg-white/60 p-8 backdrop-blur-sm dark:border-slate-700/50 dark:bg-slate-800/30">
                        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ __('100%') }}</div>
                                <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ __('OCR Accuracy') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ __('Real-time') }}</div>
                                <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ __('Plate Matching') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ __('24/7') }}</div>
                                <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ __('Monitoring') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ __('Secure') }}</div>
                                <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ __('Data Encryption') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="relative z-10 border-t border-slate-200 px-6 py-5 dark:border-slate-800/50">
                <p class="text-center text-xs text-slate-400 dark:text-slate-600">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Taxify') }}. {{ __('All rights reserved.') }}
                </p>
            </footer>
        </div>

        @fluxScripts
    </body>
</html>
