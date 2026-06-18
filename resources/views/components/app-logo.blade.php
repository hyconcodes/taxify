@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Taxify" description="Plate Recognition System" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-amber-500">
            <x-app-logo-icon class="size-5 fill-current text-primary-950" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Taxify" description="Plate Recognition System" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-amber-500">
            <x-app-logo-icon class="size-5 fill-current text-primary-950" />
        </x-slot>
    </flux:brand>
@endif
