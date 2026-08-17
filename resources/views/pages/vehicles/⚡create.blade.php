<?php

use App\Enums\VehicleInsuranceStatus;
use App\Models\VehicleOwner;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Register Vehicle')] class extends Component {
    public string $plate_number = '';
    public ?string $vin_number = null;
    public string $make = '';
    public string $model = '';
    public ?string $year = null;
    public ?string $registration_date = null;
    public ?string $color = null;
    public ?string $type = null;
    public ?string $insurance_status = null;

    public string $owner_name = '';
    public string $owner_phone = '';
    public ?string $owner_email = null;
    public ?string $owner_address = null;
    public ?string $owner_state_of_origin = null;
    public ?string $owner_driver_license_number = null;
    public ?string $owner_national_id = null;

    protected function rules(): array
    {
        return [
            'plate_number' => ['required', 'string', 'max:20', 'unique:vehicles,plate_number'],
            'vin_number' => ['nullable', 'string', 'max:50'],
            'make' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . (now()->year + 1)],
            'registration_date' => ['nullable', 'date'],
            'color' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:50'],
            'insurance_status' => ['nullable', Rule::enum(VehicleInsuranceStatus::class)],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_phone' => ['required', 'string', 'max:20'],
            'owner_email' => ['nullable', 'email', 'max:255'],
            'owner_address' => ['nullable', 'string', 'max:500'],
            'owner_state_of_origin' => ['nullable', 'string', 'max:100'],
            'owner_driver_license_number' => ['nullable', 'string', 'max:50', 'unique:vehicle_owners,driver_license_number'],
            'owner_national_id' => ['nullable', 'string', 'max:50', 'unique:vehicle_owners,national_id'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $owner = VehicleOwner::create([
            'name' => $this->owner_name,
            'phone' => $this->owner_phone,
            'email' => $this->owner_email,
            'address' => $this->owner_address,
            'state_of_origin' => $this->owner_state_of_origin,
            'driver_license_number' => $this->owner_driver_license_number,
            'national_id' => $this->owner_national_id,
        ]);

        $owner->vehicles()->create([
            'plate_number' => strtoupper($this->plate_number),
            'vin_number' => $this->vin_number,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'registration_date' => $this->registration_date,
            'color' => $this->color,
            'type' => $this->type,
            'insurance_status' => $this->insurance_status,
        ]);

        Flux::toast(variant: 'success', text: __('Vehicle registered successfully.'));

        $this->redirect(route('vehicles.index'), navigate: true);
    }
}; ?>

<div>
    <flux:heading size="xl" class="mb-4">{{ __('Register Vehicle') }}</flux:heading>

    <flux:card class="max-w-2xl space-y-6">
        <form wire:submit="save">
            <flux:fieldset legend="{{ __('Vehicle Information') }}" description="{{ __('Enter the details of the vehicle being registered.') }}" class="mb-8">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('Plate Number') }}</flux:label>
                        <flux:input wire:model="plate_number" placeholder="{{ __('e.g. ABC-1234') }}" required />
                        <flux:error name="plate_number" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('VIN Number') }}</flux:label>
                        <flux:input wire:model="vin_number" placeholder="{{ __('Vehicle identification number') }}" />
                        <flux:error name="vin_number" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Type') }}</flux:label>
                        <flux:input wire:model="type" placeholder="{{ __('e.g. Sedan, SUV, Truck') }}" />
                        <flux:error name="type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Make') }}</flux:label>
                        <flux:input wire:model="make" placeholder="{{ __('e.g. Toyota') }}" required />
                        <flux:error name="make" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Model') }}</flux:label>
                        <flux:input wire:model="model" placeholder="{{ __('e.g. Camry') }}" required />
                        <flux:error name="model" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Year') }}</flux:label>
                        <flux:input wire:model="year" type="number" placeholder="{{ __('e.g. 2020') }}" />
                        <flux:error name="year" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Registration Date') }}</flux:label>
                        <flux:input wire:model="registration_date" type="date" />
                        <flux:error name="registration_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Color') }}</flux:label>
                        <flux:input wire:model="color" placeholder="{{ __('e.g. White') }}" />
                        <flux:error name="color" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Insurance Status') }}</flux:label>
                        <flux:select wire:model="insurance_status" placeholder="{{ __('Select status') }}">
                            @foreach (\App\Enums\VehicleInsuranceStatus::cases() as $status)
                                <flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="insurance_status" />
                    </flux:field>
                </div>
            </flux:fieldset>

            <flux:fieldset legend="{{ __('Owner Information') }}" description="{{ __('Enter the details of the vehicle owner.') }}">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('Full Name') }}</flux:label>
                        <flux:input wire:model="owner_name" placeholder="{{ __('Owner full name') }}" required />
                        <flux:error name="owner_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Phone') }}</flux:label>
                        <flux:input wire:model="owner_phone" type="tel" placeholder="{{ __('Phone number') }}" required />
                        <flux:error name="owner_phone" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Email') }}</flux:label>
                        <flux:input wire:model="owner_email" type="email" placeholder="{{ __('Email address') }}" />
                        <flux:error name="owner_email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('National ID') }}</flux:label>
                        <flux:input wire:model="owner_national_id" placeholder="{{ __('National ID number') }}" />
                        <flux:error name="owner_national_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('State of Origin') }}</flux:label>
                        <flux:input wire:model="owner_state_of_origin" placeholder="{{ __('e.g. Lagos, Abuja') }}" />
                        <flux:error name="owner_state_of_origin" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Driver Licence Number') }}</flux:label>
                        <flux:input wire:model="owner_driver_license_number" placeholder="{{ __('Driver licence number') }}" />
                        <flux:error name="owner_driver_license_number" />
                    </flux:field>

                    <flux:field class="sm:col-span-2">
                        <flux:label>{{ __('Address') }}</flux:label>
                        <flux:textarea wire:model="owner_address" placeholder="{{ __('Owner address') }}" />
                        <flux:error name="owner_address" />
                    </flux:field>
                </div>
            </flux:fieldset>

            <div class="mt-6 flex items-center gap-4">
                <flux:button variant="primary" type="submit">
                    {{ __('Register Vehicle') }}
                </flux:button>
                <flux:button :href="route('vehicles.index')" wire:navigate variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>
