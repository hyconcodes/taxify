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

    public $selectedCapture = null;

    public bool $showDetail = false;

    public ?array $result = null;

    #[Computed]
    public function captures()
    {
        return PlateCapture::with(['capturer', 'vehicle.owner'])
            ->latest()
            ->get();
    }

    public function viewCapture(int $id): void
    {
        $this->selectedCapture = PlateCapture::with(['capturer', 'vehicle.owner'])
            ->findOrFail($id);
        $this->showDetail = true;
    }

    public function capture(): void
    {
        $this->validate([
            'image' => ['required', 'image', 'max:2048'],
        ]);

        $result = OcrService::fromConfig()->recognize($this->image);

        $this->image = null;
        $this->result = null;

        if ($result['plate_number'] === null) {
            Flux::toast(variant: 'warning', text: $result['message'] ?? __('No license plate could be recognized. Please upload a clear photo containing a visible car.'));

            return;
        }

        $capture = PlateCapture::create([
            'plate_number' => $result['plate_number'],
            'image_path' => $result['image_path'],
            'annotated_image_path' => $result['annotated_image_path'],
            'confidence' => $result['confidence'],
            'captured_by' => auth()->id(),
            'captured_at' => now(),
        ]);

        app(MatchPlateAction::class)->execute($capture);

        $this->result = [
            'id' => $capture->id,
            'plate_number' => $capture->plate_number,
            'confidence' => $capture->confidence,
            'annotated_image_path' => $capture->annotated_image_path,
            'image_path' => $capture->image_path,
            'is_matched' => $capture->is_matched,
        ];

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
        <flux:card id="ocr-capture-card" class="relative space-y-4">
            <flux:heading size="lg">{{ __('OCR Capture') }}</flux:heading>
            <flux:text class="mb-4">{{ __('Upload a license plate image for OCR recognition.') }}</flux:text>

            <div
                x-data="{
                    dragging: false,
                    preview: null,
                    stream: null,
                    cameraActive: false,
                    uploading: false,
                    uploadProgress: 0,

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
                        this.uploading = true;
                        this.uploadProgress = 0;
                        $wire.upload('image', file,
                            (uploaded) => {},
                            (uploaded, total) => { this.uploadProgress = Math.round((uploaded / total) * 100); },
                            () => { this.uploading = false; },
                        );
                        $wire.set('result', null);
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
                        :class="dragging ? 'border-amber-500 bg-amber-500/5' : 'border-neutral-300 dark:border-neutral-600/50'"
                        class="relative flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed p-6 text-center transition"
                        @click="!uploading ? $refs.fileInput.click() : null"
                    >
                        <template x-if="!preview && !uploading">
                            <div class="flex flex-col items-center gap-3">
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-neutral-100 dark:bg-neutral-700/50">
                                    <svg class="h-7 w-7 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">
                                        <span class="text-amber-600 dark:text-amber-400">{{ __('Click to upload') }}</span>
                                        {{ __('or drag and drop') }}
                                    </p>
                                    <p class="mt-1 text-xs text-neutral-500">{{ __('PNG, JPG up to 2MB') }}</p>
                                </div>
                            </div>
                        </template>

                        <template x-if="uploading && !preview">
                            <div class="flex flex-col items-center gap-3">
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-500/20">
                                    <svg class="h-7 w-7 animate-spin text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">{{ __('Uploading image...') }}</p>
                                    <p class="mt-1 text-xs text-neutral-500" x-text="uploadProgress + '%'"></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="uploading && preview">
                            <div class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-neutral-900/60">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="h-8 w-8 animate-spin text-amber-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <span class="text-xs text-neutral-600 dark:text-neutral-300">{{ __('Uploading...') }}</span>
                                </div>
                            </div>
                        </template>

                        <template x-if="preview && !uploading">
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

                        <div x-show="uploading" class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700">
                            <div class="h-full rounded-full bg-amber-500 transition-all duration-300" :style="'width: ' + uploadProgress + '%'"></div>
                        </div>
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
                            <button @click="stopCamera()" type="button" class="rounded-lg bg-neutral-200 px-5 py-2.5 text-sm font-medium text-neutral-700 transition hover:bg-neutral-300 dark:bg-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-600">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </div>
                </template>

                <div x-show="!preview && !cameraActive && !uploading" class="mt-3 flex justify-center" x-cloak>
                    <button @click.stop="openCamera()" type="button" class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 dark:border-neutral-600/50 dark:text-neutral-300 dark:hover:bg-neutral-700/50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                        </svg>
                        {{ __('Use Camera') }}
                    </button>
                </div>

                <div x-show="preview && !cameraActive && !uploading" class="mt-3 flex gap-3" x-cloak>
                    <button @click="openCamera()" type="button" class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 dark:border-neutral-600/50 dark:text-neutral-300 dark:hover:bg-neutral-700/50">
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

            @if ($result)
                <div class="space-y-3">
                    <flux:heading size="md">{{ __('Recognition Result') }}</flux:heading>

                    <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                        @if ($result['annotated_image_path'] || ($result['image_path'] && $result['image_path'] !== 'manual-entry'))
                            <img
                                src="{{ route('captures.image', ['capture' => $result['id']]) }}"
                                alt="{{ __('Annotated plate image') }}"
                                class="w-full rounded-t-xl border-b border-neutral-200 object-contain dark:border-neutral-700"
                            >
                        @endif

                        <div class="flex items-center justify-between gap-3 px-4 py-3">
                            <div class="min-w-0">
                                <p class="font-mono text-base font-bold">{{ $result['plate_number'] }}</p>
                                <p class="text-xs text-neutral-500">{{ __('Confidence') }}: {{ $result['confidence'] }}%</p>
                            </div>
                            <flux:badge :color="$result['is_matched'] ? 'green' : 'red'" size="sm" inset="top bottom">
                                {{ $result['is_matched'] ? __('Matched') : __('Unmatched') }}
                            </flux:badge>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex gap-3" x-cloak>
                <flux:button variant="primary" wire:click="capture" wire:loading.attr="disabled" wire:target="capture" class="relative">
                    <span wire:loading.remove wire:target="capture">{{ __('Capture & Recognize') }}</span>
                    <span wire:loading wire:target="capture" class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ __('Processing...') }}
                    </span>
                </flux:button>
            </div>

            <div wire:loading wire:target="capture" class="absolute inset-0 z-20 flex items-center justify-center rounded-xl bg-neutral-900/70">
                <div class="flex flex-col items-center gap-3">
                    <svg class="h-10 w-10 animate-spin text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <span class="text-sm font-medium text-neutral-700 dark:text-neutral-200">{{ __('Running OCR recognition...') }}</span>
                </div>
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

                <flux:button variant="primary" type="submit" class="mt-4" wire:loading.attr="disabled" wire:target="manualCapture">
                    <span wire:loading.remove wire:target="manualCapture">{{ __('Match Plate') }}</span>
                    <span wire:loading wire:target="manualCapture" class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ __('Matching...') }}
                    </span>
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
                    <flux:table.row
                        @click="if ($el.closest('table')?.querySelector('[data-selected]')) $el.closest('table').querySelector('[data-selected]').removeAttribute('data-selected'); $el.setAttribute('data-selected', ''); $wire.viewCapture({{ $capture->id }})"
                        class="cursor-pointer transition hover:bg-neutral-50 dark:hover:bg-white/5"
                    >
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

    <flux:modal wire:model="showDetail" class="w-full max-w-lg">
        @if ($selectedCapture)
            <div class="space-y-5">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Capture Detail') }}</flux:heading>
                    <flux:badge :color="$selectedCapture->is_matched ? 'green' : 'red'" size="sm">
                        {{ $selectedCapture->is_matched ? __('Matched') : __('Unmatched') }}
                    </flux:badge>
                </div>

                @if ($selectedCapture->annotated_image_path || ($selectedCapture->image_path && $selectedCapture->image_path !== 'manual-entry'))
                    <img
                        src="{{ route('captures.image', ['capture' => $selectedCapture->id]) }}"
                        alt="{{ __('Captured plate image') }}"
                        class="w-full rounded-lg border border-neutral-200 object-contain dark:border-neutral-700"
                    >
                @endif

                <div class="rounded-lg bg-neutral-50 p-4 dark:bg-neutral-800/50">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-xs font-medium uppercase tracking-wider text-neutral-500">{{ __('Plate Number') }}</span>
                            <p class="mt-0.5 font-mono text-base font-bold">{{ $selectedCapture->plate_number ?? '—' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium uppercase tracking-wider text-neutral-500">{{ __('Confidence') }}</span>
                            <p class="mt-0.5 font-mono text-base font-bold">{{ $selectedCapture->confidence }}%</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium uppercase tracking-wider text-neutral-500">{{ __('Captured By') }}</span>
                            <p class="mt-0.5">{{ $selectedCapture->capturer?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium uppercase tracking-wider text-neutral-500">{{ __('Date') }}</span>
                            <p class="mt-0.5">{{ $selectedCapture->captured_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                @if ($selectedCapture->is_matched && $selectedCapture->vehicle)
                    <flux:heading size="md">{{ __('Vehicle Information') }}</flux:heading>

                    <div class="rounded-lg bg-neutral-50 p-4 dark:bg-neutral-800/50">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-xs font-medium uppercase tracking-wider text-neutral-500">{{ __('Make') }}</span>
                                <p class="mt-0.5 font-medium">{{ $selectedCapture->vehicle->make }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-medium uppercase tracking-wider text-neutral-500">{{ __('Model') }}</span>
                                <p class="mt-0.5 font-medium">{{ $selectedCapture->vehicle->model }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-medium uppercase tracking-wider text-neutral-500">{{ __('Year') }}</span>
                                <p class="mt-0.5 font-medium">{{ $selectedCapture->vehicle->year ?? '—' }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-medium uppercase tracking-wider text-neutral-500">{{ __('Color') }}</span>
                                <p class="mt-0.5 font-medium">{{ $selectedCapture->vehicle->color ?? '—' }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-medium uppercase tracking-wider text-neutral-500">{{ __('Type') }}</span>
                                <p class="mt-0.5 font-medium">{{ $selectedCapture->vehicle->type ?? '—' }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($selectedCapture->vehicle->owner)
                        <flux:heading size="md">{{ __('Owner Information') }}</flux:heading>

                        <div class="rounded-lg bg-neutral-50 p-4 dark:bg-neutral-800/50">
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="col-span-2">
                                    <span class="text-xs font-medium uppercase tracking-wider text-neutral-500">{{ __('Name') }}</span>
                                    <p class="mt-0.5 font-medium">{{ $selectedCapture->vehicle->owner->name }}</p>
                                </div>
                                <div>
                                    <span class="text-xs font-medium uppercase tracking-wider text-neutral-500">{{ __('Phone') }}</span>
                                    <p class="mt-0.5">{{ $selectedCapture->vehicle->owner->phone ?? '—' }}</p>
                                </div>
                                <div>
                                    <span class="text-xs font-medium uppercase tracking-wider text-neutral-500">{{ __('Email') }}</span>
                                    <p class="mt-0.5">{{ $selectedCapture->vehicle->owner->email ?? '—' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-xs font-medium uppercase tracking-wider text-neutral-500">{{ __('Address') }}</span>
                                    <p class="mt-0.5">{{ $selectedCapture->vehicle->owner->address ?? '—' }}</p>
                                </div>
                                <div>
                                    <span class="text-xs font-medium uppercase tracking-wider text-neutral-500">{{ __('National ID') }}</span>
                                    <p class="mt-0.5 font-mono">{{ $selectedCapture->vehicle->owner->national_id ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                @elseif (!$selectedCapture->is_matched)
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ __('No Match Found') }}</p>
                                <p class="mt-1 text-xs text-red-600 dark:text-red-300">{{ __('This plate does not match any registered vehicle. An alert has been generated.') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex justify-end gap-3 border-t border-neutral-200 pt-4 dark:border-neutral-700">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Close') }}</flux:button>
                    </flux:modal.close>
                    <flux:button variant="primary" wire:click="$set('image', null); $set('result', null)" x-on:click="$dispatch('close-modal', 'showDetail'); setTimeout(() => document.getElementById('ocr-capture-card')?.scrollIntoView({ behavior: 'smooth' }), 200)">
                        {{ __('New Capture') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
