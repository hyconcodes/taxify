<?php

use App\Models\PlateAlert;
use App\Models\PlateCapture;
use App\Models\Vehicle;

$stats = cache()->remember('dashboard.stats', 60, function () {
    return [
        'vehicles' => Vehicle::count(),
        'captures' => PlateCapture::count(),
        'alerts' => PlateAlert::where('status', 'alert')->count(),
        'recentCaptures' => PlateCapture::with('capturer')->latest()->take(5)->get(),
    ];
});
?>

<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('Taxify Dashboard') }}</flux:heading>
            <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-medium text-amber-400 uppercase tracking-wider">
                {{ __('Law Enforcement System') }}
            </span>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <flux:card class="relative flex flex-col items-center justify-center overflow-hidden p-6">
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent"></div>
                <flux:heading size="3xl" class="text-amber-400">{{ $stats['vehicles'] }}</flux:heading>
                <flux:text class="mt-1">{{ __('Registered Vehicles') }}</flux:text>
            </flux:card>

            <flux:card class="relative flex flex-col items-center justify-center overflow-hidden p-6">
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent"></div>
                <flux:heading size="3xl" class="text-amber-400">{{ $stats['captures'] }}</flux:heading>
                <flux:text class="mt-1">{{ __('Plate Captures') }}</flux:text>
            </flux:card>

            <flux:card class="relative flex flex-col items-center justify-center overflow-hidden p-6">
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent"></div>
                <flux:heading size="3xl" class="{{ $stats['alerts'] > 0 ? 'text-red-400' : 'text-amber-400' }}">
                    {{ $stats['alerts'] }}
                </flux:heading>
                <flux:text class="mt-1">{{ __('Active Alerts') }}</flux:text>
            </flux:card>
        </div>

        <flux:card class="flex-1 overflow-hidden p-6">
            <flux:heading size="lg" class="mb-4">{{ __('Recent Captures') }}</flux:heading>

            @if ($stats['recentCaptures']->isNotEmpty())
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Plate') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column>{{ __('Date') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($stats['recentCaptures'] as $capture)
                            <flux:table.row>
                                <flux:table.cell class="font-mono">{{ $capture->plate_number ?? '—' }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge :color="$capture->is_matched ? 'green' : 'red'" size="sm" inset="top bottom">
                                        {{ $capture->is_matched ? __('Matched') : __('Unmatched') }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>{{ $capture->captured_at->format('M d, H:i') }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <flux:text class="text-neutral-500">{{ __('No captures yet.') }}</flux:text>
            @endif
        </flux:card>
    </div>
</x-layouts::app>
