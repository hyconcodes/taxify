<?php

use App\Models\PlateAlert;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Alerts')] class extends Component {
    public string $statusFilter = '';

    #[Computed]
    public function alerts()
    {
        return PlateAlert::with(['plateCapture', 'vehicle.owner', 'handler'])
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->get();
    }

    public function clearAlert(int $alertId): void
    {
        $alert = PlateAlert::findOrFail($alertId);
        $alert->update([
            'status' => 'cleared',
            'handled_by' => auth()->id(),
            'handled_at' => now(),
        ]);

        Flux::toast(variant: 'success', text: __('Alert cleared.'));
    }

    #[Computed]
    public function alertCount(): array
    {
        return [
            'total' => PlateAlert::count(),
            'active' => PlateAlert::where('status', 'alert')->count(),
        ];
    }
}; ?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Alerts') }}</flux:heading>
            <flux:text class="mt-1">
                {{ __(':active active out of :total total', ['active' => $this->alertCount['active'], 'total' => $this->alertCount['total']]) }}
            </flux:text>
        </div>
    </div>

    <div class="mb-6 flex items-center gap-4">
        <flux:select wire:model.live="statusFilter" class="max-w-xs">
            <option value="">{{ __('All Statuses') }}</option>
            <option value="alert">{{ __('Alert') }}</option>
            <option value="cleared">{{ __('Cleared') }}</option>
        </flux:select>
    </div>

    <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Plate') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Notes') }}</flux:table.column>
                <flux:table.column>{{ __('Handled By') }}</flux:table.column>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column>{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->alerts as $alert)
                    <flux:table.row>
                        <flux:table.cell class="font-mono font-bold">
                            {{ $alert->plateCapture?->plate_number ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$alert->status === 'alert' ? 'red' : 'green'" size="sm" inset="top bottom">
                                {{ $alert->status === 'alert' ? __('Alert') : __('Cleared') }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="max-w-xs truncate">{{ $alert->notes ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $alert->handler?->name ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $alert->created_at->format('M d, H:i') }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($alert->status === 'alert')
                                <flux:button wire:click="clearAlert({{ $alert->id }})" size="sm" variant="subtle" icon="check">
                                    {{ __('Clear') }}
                                </flux:button>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-neutral-500">
                            {{ __('No alerts found.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
