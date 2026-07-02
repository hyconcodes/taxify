<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Organizational System')" :description="__('This is a restricted law enforcement system. Account management and vehicle registration are handled internally by the organization.')" />

        <div class="flex flex-col items-center gap-4 rounded-xl border border-amber-500/20 bg-amber-500/5 p-6 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-500/20">
                <svg class="h-7 w-7 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-amber-400">{{ __('Need to Register a Vehicle?') }}</p>
                <p class="mt-1 text-xs text-slate-400">
                    {{ __('Vehicle owners should visit their nearest law enforcement station with the required documents. The organization will handle registration on your behalf.') }}
                </p>
            </div>
        </div>

        <flux:button :href="route('home')" variant="primary" class="w-full">
            {{ __('Back to Home') }}
        </flux:button>
    </div>
</x-layouts::auth>