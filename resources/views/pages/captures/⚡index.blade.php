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

        $result = OcrService::fromConfig()->recognize($this->image);

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

            <div
                x-data="{
                    dragging: false,
                    preview: null,
                    stream: null,
                    cameraActive: false,

                    handleDrop(e) {
                        e.preventDefault();
                        this.dragging = false;
                        if (e.dataTransfer.files.length) this.setFile(e.dataTransfer.files[0]);
                    },

                    handleFileInput(e) {
                        if (e.target.files.length) this.setFile(e.target.files[0]);
                    },

                    setFile(file) {
                        if (!file.type.startsWith('image/')) return;
                        $wire.upload('image', file);
                        const reader = new FileReader();
                        reader.onload = (e) => { this.preview = e.target.result; };
                        reader.readAsDataURL(file);
                    },

                    async openCamera() {
                        this.cameraActive = true;
                        try {
                            this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                            this.$refs.video.srcObject = this.stream;
                            this.$refs.video.play();
                        } catch {
                            this.cameraActive = false;
                            alert('{{ __('Camera access denied or not available.') }}');
                        }
                    },

                    captureFromCamera() {
                        const video = this.$refs.video;
                        const canvas = document.createElement('canvas');
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        canvas.getContext('2d').drawImage(video, 0, 0);
                        canvas.toBlob((blob) => {
                            const file = new File([blob], 'camera-capture.jpg', { type: 'image/jpeg' });
                            this.setFile(file);
                        }, 'image/jpeg');
                        this.stopCamera();
                    },

                    stopCamera() {
                        if (this.stream) {
                            this.stream.getTracks().forEach(t => t.stop());
                            this.stream = null;
                        }
                        this.cameraActive = false;
                    }
                }"
                class="relative"
            >
                <template x-if="!cameraActive">
                    <div
                        @drop.prevent="handleDrop"
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        :class="dragging ? 'border-amber-500 bg-amber-500/5' : 'border-neutral-600/50 dark:border-neutral-600/50'"
                        class="relative flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed p-6 text-center transition"
                        @click="$refs.fileInput.click()"
                    >
                        <template x-if="!preview">
                            <div class="flex flex-col items-center gap-3">
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-neutral-700/50">
                                    <svg class="h-7 w-7 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-neutral-300">
                                        <span class="text-amber-400">{{ __('Click to upload') }}</span>
                                        {{ __('or drag and drop') }}
                                    </p>
                                    <p class="mt-1 text-xs text-neutral-500">{{ __('PNG, JPG up to 2MB') }}</p>
                                </div>
                            </div>
                        </template>

                        <template x-if="preview">
                            <div class="relative w-full">
                                <img :src="preview" class="max-h-40 w-full rounded-lg object-contain" />
                                <button @click.stop="preview = null; $wire.set('image', null)" type="button" class="absolute top-2 right-2 flex h-7 w-7 items-center justify-center rounded-full bg-neutral-900/80 text-neutral-400 transition hover:bg-red-500 hover:text-white">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <input type="file" x-ref="fileInput" @change="handleFileInput" accept="image/*" class="hidden" />
                    </div>
                </template>

                <template x-if="cameraActive">
                    <div class="relative flex flex-col items-center gap-4">
                        <video x-ref="video" autoplay playsinline class="w-full rounded-xl"></video>
                        <div class="flex gap-3">
                            <button @click="captureFromCamera" type="button" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-neutral-950 transition hover:bg-amber-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" />
                                </svg>
                                {{ __('Capture') }}
                            </button>
                            <button @click="stopCamera()" type="button" class="rounded-lg bg-neutral-700 px-5 py-2.5 text-sm font-medium text-neutral-300 transition hover:bg-neutral-600">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </div>
                </template>

                <div x-show="!preview && !cameraActive" class="mt-3 flex justify-center" x-cloak>
                    <button @click.stop="openCamera()" type="button" class="inline-flex items-center gap-2 rounded-lg border border-neutral-600/50 px-4 py-2 text-sm font-medium text-neutral-300 transition hover:bg-neutral-700/50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                        </svg>
                        {{ __('Use Camera') }}
                    </button>
                </div>

                <div x-show="preview && !cameraActive" class="mt-3 flex gap-3" x-cloak>
                    <button @click="openCamera()" type="button" class="inline-flex items-center gap-2 rounded-lg border border-neutral-600/50 px-4 py-2 text-sm font-medium text-neutral-300 transition hover:bg-neutral-700/50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" />
                        </svg>
                        {{ __('Retake with Camera') }}
                    </button>
                </div>
            </div>

            @error('image')
                <flux:error>{{ $message }}</flux:error>
            @enderror

            @if ($image)
                <div class="mt-2">
                    <img src="{{ $image->temporaryUrl() }}" class="max-h-40 rounded-lg border border-neutral-200 dark:border-neutral-700" />
                </div>
            @endif

            <div class="flex gap-3" x-cloak>
                <flux:button variant="primary" wire:click="capture" wire:loading.attr="disabled">
                    {{ __('Capture & Recognize') }}
                </flux:button>
            </div>
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
