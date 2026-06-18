<?php

use App\Models\Vehicle;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Registered Vehicles')] class extends Component {
    public string $search = '';

    #[Computed]
    public function vehicles()
    {
        return Vehicle::with('owner')
            ->when($this->search, fn ($q) => $q->where('plate_number', 'like', "%{$this->search}%")
                ->orWhere('make', 'like', "%{$this->search}%")
                ->orWhere('model', 'like', "%{$this->search}%"))
            ->latest()
            ->get();
    }
}; ?>

<div>
    <flux:heading size="xl" class="mb-4">{{ __('Registered Vehicles') }}</flux:heading>

    <div class="mb-6 flex items-center justify-between gap-4">
        <flux:input wire:model.live="search" placeholder="{{ __('Search by plate, make, or model...') }}" class="max-w-md" />
        <flux:button :href="route('vehicles.create')" wire:navigate icon="plus">
            {{ __('Register Vehicle') }}
        </flux:button>
    </div>

    <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Plate Number') }}</flux:table.column>
                <flux:table.column>{{ __('Make') }}</flux:table.column>
                <flux:table.column>{{ __('Model') }}</flux:table.column>
                <flux:table.column>{{ __('Year') }}</flux:table.column>
                <flux:table.column>{{ __('Color') }}</flux:table.column>
                <flux:table.column>{{ __('Owner') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->vehicles as $vehicle)
                    <flux:table.row>
                        <flux:table.cell class="font-mono font-bold">{{ $vehicle->plate_number }}</flux:table.cell>
                        <flux:table.cell>{{ $vehicle->make }}</flux:table.cell>
                        <flux:table.cell>{{ $vehicle->model }}</flux:table.cell>
                        <flux:table.cell>{{ $vehicle->year }}</flux:table.cell>
                        <flux:table.cell>{{ $vehicle->color }}</flux:table.cell>
                        <flux:table.cell>{{ $vehicle->owner->name }}</flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-neutral-500">
                            {{ __('No vehicles registered yet.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
