<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Account Registration Unavailable')" :description="__('User registration is currently disabled. If you are a vehicle owner, please log in and use the system to register your vehicle.')" />

        <div class="flex flex-col items-center gap-4 rounded-xl border border-amber-500/20 bg-amber-500/5 p-6 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-500/20">
                <svg class="h-7 w-7 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.1V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-amber-400">{{ __('Vehicle Registration Available') }}</p>
                <p class="mt-1 text-xs text-zinc-400">
                    {{ __('Once logged in, you can register your vehicle plate number and owner details through the Vehicles section.') }}
                </p>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <flux:button :href="route('login')" variant="primary" class="w-full">
                {{ __('Log in') }}
            </flux:button>

            <flux:button :href="route('home')" variant="ghost" class="w-full">
                {{ __('Back to Home') }}
            </flux:button>
        </div>
    </div>
</x-layouts::auth>