<?php

use App\Actions\Plate\MatchPlateAction;
use App\Models\PlateCapture;
use App\Services\OcrService;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Plate Captures')] class extends Component {
    use WithFileUploads;

    public $image = null;

    public string $manual_plate = '';

    #[Computed]
    public function captures()
    {
        return PlateCapture::with('capturer')
            ->latest()
            ->get();
    }

    public function capture(): void
    {
        $this->validate([
            'image' => ['required', 'image', 'max:2048'],
        ]);

        $ocr = app(OcrService::class);
        $result = $ocr->recognize($this->image);

        $capture = PlateCapture::create([
            'plate_number' => $result['plate_number'],
            'image_path' => $result['image_path'],
            'confidence' => $result['confidence'],
            'captured_by' => auth()->id(),
            'captured_at' => now(),
        ]);

        app(MatchPlateAction::class)->execute($capture);

        $this->image = null;

        Flux::toast(variant: $capture->is_matched ? 'success' : 'warning', text: $capture->is_matched
            ? __('Plate recognized and matched successfully.')
            : __('Plate recognized but no match found. Alert generated.'));
    }

    public function manualCapture(): void
    {
        $this->validate([
            'manual_plate' => ['required', 'string', 'max:20'],
        ]);

        $capture = PlateCapture::create([
            'plate_number' => strtoupper($this->manual_plate),
            'image_path' => 'manual-entry',
            'confidence' => 100,
            'captured_by' => auth()->id(),
            'captured_at' => now(),
        ]);

        app(MatchPlateAction::class)->execute($capture);

        $this->manual_plate = '';

        Flux::toast(variant: $capture->is_matched ? 'success' : 'warning', text: $capture->is_matched
            ? __('Plate matched successfully.')
            : __('No match found. Alert generated.'));
    }
}; ?>

<div>
    <flux:heading size="xl" class="mb-4">{{ __('Plate Captures') }}</flux:heading>

    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <flux:card class="space-y-4">
            <flux:heading size="lg">{{ __('OCR Capture') }}</flux:heading>
            <flux:text class="mb-4">{{ __('Upload a license plate image for OCR recognition.') }}</flux:text>

            <form wire:submit="capture">
                <flux:field>
                    <flux:label>{{ __('Plate Image') }}</flux:label>
                    <flux:input type="file" wire:model="image" accept="image/*" />
                    <flux:error name="image" />
                </flux:field>

                @if ($image)
                    <div class="mt-2">
                        <img src="{{ $image->temporaryUrl() }}" class="max-h-40 rounded-lg border border-neutral-200 dark:border-neutral-700" />
                    </div>
                @endif

                <flux:button variant="primary" type="submit" class="mt-4" wire:loading.attr="disabled">
                    {{ __('Capture & Recognize') }}
                </flux:button>
            </form>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="lg">{{ __('Manual Entry') }}</flux:heading>
            <flux:text class="mb-4">{{ __('Enter a plate number manually for matching.') }}</flux:text>

            <form wire:submit="manualCapture">
                <flux:field>
                    <flux:label>{{ __('Plate Number') }}</flux:label>
                    <flux:input wire:model="manual_plate" placeholder="{{ __('Enter plate number') }}" />
                    <flux:error name="manual_plate" />
                </flux:field>

                <flux:button variant="primary" type="submit" class="mt-4" wire:loading.attr="disabled">
                    {{ __('Match Plate') }}
                </flux:button>
            </form>
        </flux:card>
    </div>

    <flux:heading size="lg" class="mb-4">{{ __('Capture History') }}</flux:heading>

    <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Plate Number') }}</flux:table.column>
                <flux:table.column>{{ __('Confidence') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Captured By') }}</flux:table.column>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->captures as $capture)
                    <flux:table.row>
                        <flux:table.cell class="font-mono font-bold">{{ $capture->plate_number ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $capture->confidence }}%</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$capture->is_matched ? 'green' : 'red'" size="sm" inset="top bottom">
                                {{ $capture->is_matched ? __('Matched') : __('Unmatched') }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $capture->capturer?->name ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $capture->captured_at->format('M d, H:i') }}</flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-neutral-500">
                            {{ __('No captures yet.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
