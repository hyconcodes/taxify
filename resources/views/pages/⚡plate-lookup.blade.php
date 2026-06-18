<?php

use App\Models\Vehicle;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Plate Lookup')] class extends Component {
    public string $plate = '';
    public bool $searched = false;

    #[Computed]
    public function result(): ?Vehicle
    {
        if (! $this->searched || blank($this->plate)) {
            return null;
        }

        return Vehicle::with('owner')
            ->where('plate_number', strtoupper($this->plate))
            ->first();
    }

    public function search(): void
    {
        $this->validate(['plate' => ['required', 'string', 'max:20']]);
        $this->searched = true;
    }
}; ?>

<div>
    <flux:heading size="xl" class="mb-4">{{ __('Plate Lookup') }}</flux:heading>

    <flux:card class="mb-6 max-w-xl space-y-4">
        <flux:text>{{ __('Enter a plate number to look up its registration details.') }}</flux:text>

        <form wire:submit="search" class="flex items-end gap-4">
            <flux:field class="flex-1">
                <flux:label>{{ __('Plate Number') }}</flux:label>
                <flux:input wire:model="plate" placeholder="{{ __('Enter plate number') }}" />
                <flux:error name="plate" />
            </flux:field>

            <flux:button variant="primary" type="submit" icon="magnifying-glass">
                {{ __('Search') }}
            </flux:button>
        </form>
    </flux:card>

    @if ($this->searched)
        @if ($this->result)
            <flux:card class="max-w-xl space-y-6">
                <flux:heading size="lg">{{ __('Vehicle Details') }}</flux:heading>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:text class="text-sm text-neutral-500">{{ __('Plate Number') }}</flux:text>
                        <flux:heading class="font-mono text-xl">{{ $this->result->plate_number }}</flux:heading>
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500">{{ __('Status') }}</flux:text>
                        <flux:badge color="green" size="sm" inset="top bottom">{{ __('Registered') }}</flux:badge>
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500">{{ __('Make') }}</flux:text>
                        <flux:heading>{{ $this->result->make }}</flux:heading>
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500">{{ __('Model') }}</flux:text>
                        <flux:heading>{{ $this->result->model }}</flux:heading>
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500">{{ __('Year') }}</flux:text>
                        <flux:heading>{{ $this->result->year ?? '—' }}</flux:heading>
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500">{{ __('Color') }}</flux:text>
                        <flux:heading>{{ $this->result->color ?? '—' }}</flux:heading>
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500">{{ __('Type') }}</flux:text>
                        <flux:heading>{{ $this->result->type ?? '—' }}</flux:heading>
                    </div>
                </div>

                <flux:separator />

                <flux:heading size="lg">{{ __('Owner Details') }}</flux:heading>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:text class="text-sm text-neutral-500">{{ __('Name') }}</flux:text>
                        <flux:heading>{{ $this->result->owner->name }}</flux:heading>
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500">{{ __('Phone') }}</flux:text>
                        <flux:heading>{{ $this->result->owner->phone }}</flux:heading>
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500">{{ __('National ID') }}</flux:text>
                        <flux:heading>{{ $this->result->owner->national_id ?? '—' }}</flux:heading>
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500">{{ __('Email') }}</flux:text>
                        <flux:heading>{{ $this->result->owner->email ?? '—' }}</flux:heading>
                    </div>
                    <div class="col-span-2">
                        <flux:text class="text-sm text-neutral-500">{{ __('Address') }}</flux:text>
                        <flux:heading>{{ $this->result->owner->address ?? '—' }}</flux:heading>
                    </div>
                </div>
            </flux:card>
        @else
            <flux:card class="max-w-xl">
                <flux:heading size="lg" class="text-red-600 dark:text-red-400">{{ __('Not Found') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('No registered vehicle matches the plate number ":plate".', ['plate' => strtoupper($this->plate)]) }}
                </flux:text>
            </flux:card>
        @endif
    @endif
</div>
