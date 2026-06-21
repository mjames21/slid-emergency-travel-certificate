{{-- FILE: resources/views/livewire/staff/applications/create.blade.php --}}
<div class="space-y-6">
    @if (session('success') && $step !== 2)
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-3">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-bold text-gray-900">New Application</h1>

                    @if (($passengerHistory['has_duplicate_passenger_records'] ?? false) === true)
                        <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-800">
                            Duplicate Passport Records
                        </span>
                    @endif

                    @if (($passengerHistory['has_fraud_history'] ?? false) === true)
                        <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-800">
                            Fraud History
                        </span>
                    @endif

                    @if (($passengerHistory['is_repeat_traveler'] ?? false) === true)
                        <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-800">
                            Repeat Traveler
                        </span>
                    @endif
                </div>

                <p class="mt-1 text-sm text-gray-600">
                    Capture passport biodata first, review MRZ output, then complete the visa-on-arrival application.
                </p>
            </div>

            <a
                href="{{ route('staff.applications.index') }}"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700"
            >
                Back to Applications
            </a>
        </div>

        @if (($passengerHistory['has_duplicate_passenger_records'] ?? false) === true)
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                Warning: multiple passenger records already use this passport number. Review the traveler history carefully before continuing.
            </div>
        @endif
    </div>

    @php
        $steps = [
            1 => 'Passport Capture',
            2 => 'Traveler Details',
            3 => 'Travel Details',
            4 => 'Host & Address',
            5 => 'Review & Submit',
        ];
    @endphp

    <div class="rounded-xl bg-white p-4 shadow">
        <div class="grid gap-3 md:grid-cols-5">
            @foreach ($steps as $number => $label)
                <div class="rounded-lg border px-4 py-3 {{ $step === $number ? 'border-slate-900 bg-slate-900 text-white' : 'border-gray-200 bg-gray-50 text-gray-700' }}">
                    <div class="text-xs uppercase tracking-wide opacity-80">Step {{ $number }}</div>
                    <div class="mt-1 text-sm font-semibold">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
        @if ($step === 1)
            @php
                $passportPreviewSrc = !empty($passport_biodata_image)
                    ? $passport_biodata_image->temporaryUrl()
                    : ($passport_biodata_preview_url ?: null);

                $mrzPreviewSrc = !empty($passport_mrz_crop_image)
                    ? $passport_mrz_crop_image->temporaryUrl()
                    : ($passport_mrz_crop_preview_url ?: null);
            @endphp

            <div class="rounded-xl bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Passport Capture & Identity Review</h2>

                <div class="mt-4 grid gap-6 xl:grid-cols-2">
                    <div class="space-y-4">
                        <div class="rounded-xl border border-gray-200 p-4">
                            <x-forms.passport-biodata-capture />
                        </div>

                        @error('passport_biodata_image')
                            <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                {{ $message }}
                            </div>
                        @enderror

                        @error('passport_mrz_crop_image')
                            <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                {{ $message }}
                            </div>
                        @enderror

                        @error('passport_biodata_path')
                            <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                {{ $message }}
                            </div>
                        @enderror

                        @error('passport_mrz_crop_path')
                            <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-xl border border-gray-200 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">Capture Status</h3>
                                    <p class="text-xs text-gray-500">
                                        Review the biodata image and MRZ crop before moving to traveler details.
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    @if ($passportPreviewSrc || $mrzPreviewSrc)
                                        <button
                                            type="button"
                                            wire:click="readPassportMrz"
                                            class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700"
                                        >
                                            Read MRZ
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="removePassportBiodata"
                                            class="rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-xs font-medium text-red-800"
                                        >
                                            Remove Capture
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                    <div class="text-xs uppercase tracking-wide text-gray-500">Biodata Image</div>
                                    <div class="mt-2 text-sm font-medium text-gray-900">
                                        {{ $passportPreviewSrc ? 'Captured / Uploaded' : 'Not available yet' }}
                                    </div>
                                </div>

                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                    <div class="text-xs uppercase tracking-wide text-gray-500">MRZ Crop</div>
                                    <div class="mt-2 text-sm font-medium text-gray-900">
                                        {{ $mrzPreviewSrc ? 'Available' : 'Not available yet' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($passportPreviewSrc || $mrzPreviewSrc)
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="rounded-xl border border-gray-200 p-4">
                                    <h3 class="text-sm font-semibold text-gray-900">Passport Biodata Preview</h3>

                                    <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                        @if ($passportPreviewSrc)
                                            <img
                                                src="{{ $passportPreviewSrc }}"
                                                alt="Passport biodata preview"
                                                class="max-h-72 w-full rounded-lg object-contain"
                                            >
                                        @else
                                            <div class="text-sm text-gray-500">No biodata image yet.</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 p-4">
                                    <h3 class="text-sm font-semibold text-gray-900">MRZ Crop Preview</h3>

                                    <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                        @if ($mrzPreviewSrc)
                                            <img
                                                src="{{ $mrzPreviewSrc }}"
                                                alt="Passport MRZ crop preview"
                                                class="max-h-72 w-full rounded-lg object-contain"
                                            >
                                        @else
                                            <div class="text-sm text-gray-500">No MRZ crop yet.</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ($step === 2)
            <div class="rounded-xl bg-white p-6 shadow">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Traveler Details</h2>
                        <p class="text-sm text-gray-500">Confirm or adjust the extracted traveler data.</p>
                    </div>
                </div>

                @if ($stepSuccessMessage)
                    <div
                        x-data
                        x-init="setTimeout(() => $wire.clearStepSuccessMessage(), 2500)"
                        class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
                    >
                        {{ $stepSuccessMessage }}
                    </div>
                @endif

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Airport</label>
                        <select wire:model.live="airport_id" class="w-full rounded-lg border-gray-300 shadow-sm">
                            <option value="">Select airport</option>
                            @foreach ($airports as $airport)
                                <option value="{{ $airport->id }}">{{ $airport->name }} ({{ $airport->code }})</option>
                            @endforeach
                        </select>
                        @error('airport_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Desk</label>
                        <select wire:model="desk_id" class="w-full rounded-lg border-gray-300 shadow-sm">
                            <option value="">Select desk</option>
                            @foreach ($desks as $desk)
                                <option value="{{ $desk->id }}">{{ $desk->name }}</option>
                            @endforeach
                        </select>
                        @error('desk_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Surname</label>
                        <input type="text" wire:model="surname" class="w-full rounded-lg border-gray-300 shadow-sm">
                        @error('surname')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Given Names</label>
                        <input type="text" wire:model="given_names" class="w-full rounded-lg border-gray-300 shadow-sm">
                        @error('given_names')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Nationality</label>
                        <input
                            type="text"
                            list="nationality-list"
                            wire:model.live="nationality"
                            class="w-full rounded-lg border-gray-300 shadow-sm"
                            placeholder="Select or type nationality"
                        >
                        <datalist id="nationality-list">
                            @foreach ($nationalities as $nationalityOption)
                                <option value="{{ $nationalityOption->name }}"></option>
                            @endforeach
                        </datalist>
                        @error('nationality')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Nationality Code</label>
                        <select wire:model.live="nationality_code" class="w-full rounded-lg border-gray-300 shadow-sm">
                            <option value="">Select code</option>
                            @foreach ($nationalities as $nationalityOption)
                                <option value="{{ $nationalityOption->code }}">
                                    {{ $nationalityOption->code }} — {{ $nationalityOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('nationality_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Passport Number</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="passport_number"
                            class="w-full rounded-lg border-gray-300 shadow-sm"
                            placeholder="Passport number"
                        >
                        @error('passport_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Passport Expiry Year</label>
                        <input type="number" wire:model="passport_expiry_year" class="w-full rounded-lg border-gray-300 shadow-sm">
                        @error('passport_expiry_year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Passport Expiry Month</label>
                            <input type="number" wire:model="passport_expiry_month" class="w-full rounded-lg border-gray-300 shadow-sm">
                            @error('passport_expiry_month')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Passport Expiry Day</label>
                            <input type="number" wire:model="passport_expiry_day" class="w-full rounded-lg border-gray-300 shadow-sm">
                            @error('passport_expiry_day')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Sex</label>
                        <select wire:model="sex" class="w-full rounded-lg border-gray-300 shadow-sm">
                            <option value="">Select sex</option>
                            @foreach ($sexOptions as $sexOption)
                                <option value="{{ $sexOption->code }}">{{ $sexOption->name }}</option>
                            @endforeach
                        </select>
                        @error('sex')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Date of Birth Year</label>
                        <input type="number" wire:model="date_of_birth_year" class="w-full rounded-lg border-gray-300 shadow-sm">
                        @error('date_of_birth_year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Date of Birth Month</label>
                            <input type="number" wire:model="date_of_birth_month" class="w-full rounded-lg border-gray-300 shadow-sm">
                            @error('date_of_birth_month')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Date of Birth Day</label>
                            <input type="number" wire:model="date_of_birth_day" class="w-full rounded-lg border-gray-300 shadow-sm">
                            @error('date_of_birth_day')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        @include('livewire.staff.applications.partials.traveler-history')
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Country of Birth</label>
                        <input
                            type="text"
                            list="country-list"
                            wire:model.live="country_of_birth"
                            class="w-full rounded-lg border-gray-300 shadow-sm"
                            placeholder="Select or type country of birth"
                        >
                        @error('country_of_birth')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Country of Residence</label>
                        <input
                            type="text"
                            list="country-list"
                            wire:model.live="country_of_residence"
                            class="w-full rounded-lg border-gray-300 shadow-sm"
                            placeholder="Select or type country of residence"
                        >
                        @error('country_of_residence')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <datalist id="country-list">
                        @foreach ($nationalities as $countryOption)
                            <option value="{{ $countryOption->name }}"></option>
                        @endforeach
                    </datalist>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Occupation</label>
                        <input type="text" wire:model="occupation" class="w-full rounded-lg border-gray-300 shadow-sm">
                        @error('occupation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" wire:model="email" class="w-full rounded-lg border-gray-300 shadow-sm">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" wire:model="phone" class="w-full rounded-lg border-gray-300 shadow-sm">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        @endif

        @if ($step === 3)
            <div class="rounded-xl bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Travel Details</h2>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Purpose of Visit</label>
                        <select wire:model="purpose_of_visit" class="w-full rounded-lg border-gray-300 shadow-sm">
                            <option value="">Select purpose</option>
                            @foreach ($purposesOfVisit as $purpose)
                                <option value="{{ $purpose->name }}">{{ $purpose->name }}</option>
                            @endforeach
                        </select>
                        @error('purpose_of_visit')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($pointsOfEntry->isNotEmpty())
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Point of Entry</label>
                            <select wire:model="point_of_entry_id" class="w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="">Select point of entry</option>
                                @foreach ($pointsOfEntry as $pointOfEntry)
                                    <option value="{{ $pointOfEntry->id }}">{{ $pointOfEntry->name }}</option>
                                @endforeach
                            </select>
                            @error('point_of_entry_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Period of Stay (Days)</label>
                        <input type="number" min="1" max="365" wire:model.live="period_of_stay_days" class="w-full rounded-lg border-gray-300 shadow-sm">
                        @error('period_of_stay_days')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Period of Stay Text</label>
                        <input type="text" wire:model="period_of_stay_text" readonly class="w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Arrival Date</label>
                        <input type="date" wire:model.live="arrival_date" class="w-full rounded-lg border-gray-300 shadow-sm">
                        @error('arrival_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Valid From</label>
                        <input type="date" wire:model.live="valid_from" class="w-full rounded-lg border-gray-300 shadow-sm">
                        @error('valid_from')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Valid Until</label>
                        <input type="date" wire:model="valid_until" readonly class="w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm">
                        @error('valid_until')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Flight Carrier</label>
                        <input
                            type="text"
                            list="staff-flight-carrier-list"
                            wire:model="flight_carrier"
                            class="w-full rounded-lg border-gray-300 shadow-sm"
                            placeholder="Start typing airline name"
                        >
                        <datalist id="staff-flight-carrier-list">
                            @foreach ($flightCarriers as $carrier)
                                <option value="{{ $carrier['name'] }}">
                                    {{ $carrier['code'] ?? '' }}
                                </option>
                            @endforeach
                        </datalist>
                        <p class="mt-1 text-xs text-gray-500">Search scheduled Sierra Leone carriers serving Freetown International Airport. For charter or unlisted movement, type the carrier name exactly from the ticket.</p>
                        @error('flight_carrier')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Flight Number</label>
                        <input type="text" wire:model="flight_number" class="w-full rounded-lg border-gray-300 shadow-sm">
                        @error('flight_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Flight Details</label>
                        <textarea wire:model="flight_details" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
                        @error('flight_details')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        @endif

        @if ($step === 4)
            <div class="rounded-xl bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Host & Address</h2>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Host Name</label>
                        <input type="text" wire:model="host_name" class="w-full rounded-lg border-gray-300 shadow-sm">
                        @error('host_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Host Address</label>
                        <input type="text" wire:model="host_address" class="w-full rounded-lg border-gray-300 shadow-sm">
                        @error('host_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Destination Address</label>
                        <textarea wire:model="destination_address" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
                        @error('destination_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Destination Phone</label>
                        <input type="text" wire:model="destination_phone" class="w-full rounded-lg border-gray-300 shadow-sm">
                        @error('destination_phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Migration & Assistance Observations</label>
                        <textarea wire:model="remarks" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
                        @error('remarks')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        @endif

        @if ($step === 5)
            @php
                $reviewPassportPreviewSrc = !empty($passport_biodata_image)
                    ? $passport_biodata_image->temporaryUrl()
                    : ($passport_biodata_preview_url ?: null);
            @endphp

            <div class="rounded-xl bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Review & Submit</h2>

                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-emerald-950">ICAO / IATA / IOM Readiness</h3>
                            <p class="mt-1 text-sm text-emerald-800">Operational checks before submission. Warnings can proceed when a manual review is appropriate.</p>
                        </div>
                        <div class="text-sm font-semibold text-emerald-950">
                            {{ $standardsReadiness['summary']['score'] ?? 0 }}% ready
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 lg:grid-cols-3">
                        @foreach (($standardsReadiness['sections'] ?? []) as $section)
                            <div class="rounded-lg border border-emerald-200 bg-white p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="font-semibold text-gray-900">{{ $section['label'] }}</div>
                                    <div class="text-xs font-semibold text-gray-500">
                                        {{ $section['counts']['pass'] ?? 0 }}/{{ $section['counts']['total'] ?? 0 }}
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-600">{{ $section['description'] }}</p>

                                <div class="mt-3 space-y-2">
                                    @foreach ($section['items'] as $item)
                                        @php
                                            $statusClass = match ($item['status']) {
                                                'pass' => 'border-green-200 bg-green-50 text-green-800',
                                                'warn' => 'border-amber-200 bg-amber-50 text-amber-800',
                                                default => 'border-red-200 bg-red-50 text-red-800',
                                            };
                                        @endphp
                                        <div class="rounded border px-2.5 py-2 text-xs {{ $statusClass }}">
                                            <div class="font-semibold">{{ strtoupper($item['status']) }} · {{ $item['label'] }}</div>
                                            <div class="mt-0.5 opacity-90">{{ $item['detail'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 grid gap-6 lg:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Passport & Identity Review</h3>

                        <div class="mt-3 space-y-3 text-sm">
                            <div><span class="text-gray-500">Passport Capture:</span> <span class="font-medium text-gray-900">{{ $reviewPassportPreviewSrc ? 'Available' : 'Not available' }}</span></div>
                            <div><span class="text-gray-500">MRZ Crop:</span> <span class="font-medium text-gray-900">{{ $passport_mrz_crop_preview_url || !empty($passport_mrz_crop_image) ? 'Available' : 'Not available' }}</span></div>
                            <div><span class="text-gray-500">MRZ Read:</span> <span class="font-medium text-gray-900">{{ $passport_mrz_raw_text ? 'Completed' : 'Not completed' }}</span></div>

                            @if ($reviewPassportPreviewSrc)
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                    <img
                                        src="{{ $reviewPassportPreviewSrc }}"
                                        alt="Passport biodata preview"
                                        class="max-h-56 w-full rounded-lg object-contain"
                                    >
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Traveler</h3>
                        <div class="mt-3 space-y-2 text-sm">
                            <div><span class="text-gray-500">Surname:</span> <span class="font-medium text-gray-900">{{ $surname ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Given Names:</span> <span class="font-medium text-gray-900">{{ $given_names ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Nationality:</span> <span class="font-medium text-gray-900">{{ $nationality ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Nationality Code:</span> <span class="font-medium text-gray-900">{{ $nationality_code ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Passport Number:</span> <span class="font-medium text-gray-900">{{ $passport_number ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Sex:</span> <span class="font-medium text-gray-900">{{ $sex ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Date of Birth:</span> <span class="font-medium text-gray-900">{{ $date_of_birth_year && $date_of_birth_month && $date_of_birth_day ? sprintf('%04d-%02d-%02d', $date_of_birth_year, $date_of_birth_month, $date_of_birth_day) : '—' }}</span></div>
                            <div><span class="text-gray-500">Passport Expiry:</span> <span class="font-medium text-gray-900">{{ $passport_expiry_year && $passport_expiry_month && $passport_expiry_day ? sprintf('%04d-%02d-%02d', $passport_expiry_year, $passport_expiry_month, $passport_expiry_day) : '—' }}</span></div>
                            <div><span class="text-gray-500">Email:</span> <span class="font-medium text-gray-900">{{ $email ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Phone:</span> <span class="font-medium text-gray-900">{{ $phone ?: '—' }}</span></div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Travel</h3>
                        <div class="mt-3 space-y-2 text-sm">
                            <div><span class="text-gray-500">Airport:</span> <span class="font-medium text-gray-900">{{ optional($airports->firstWhere('id', $airport_id))->name ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Desk:</span> <span class="font-medium text-gray-900">{{ optional($desks->firstWhere('id', $desk_id))->name ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Purpose:</span> <span class="font-medium text-gray-900">{{ $purpose_of_visit ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Point of Entry:</span> <span class="font-medium text-gray-900">{{ optional($pointsOfEntry->firstWhere('id', $point_of_entry_id))->name ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Arrival Date:</span> <span class="font-medium text-gray-900">{{ $arrival_date ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Valid From:</span> <span class="font-medium text-gray-900">{{ $valid_from ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Valid Until:</span> <span class="font-medium text-gray-900">{{ $valid_until ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Period of Stay:</span> <span class="font-medium text-gray-900">{{ $period_of_stay_text ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Flight Carrier:</span> <span class="font-medium text-gray-900">{{ $flight_carrier ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Flight Number:</span> <span class="font-medium text-gray-900">{{ $flight_number ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Flight Details:</span> <span class="font-medium text-gray-900">{{ $flight_details ?: '—' }}</span></div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Host & Destination</h3>
                        <div class="mt-3 space-y-2 text-sm">
                            <div><span class="text-gray-500">Host Name:</span> <span class="font-medium text-gray-900">{{ $host_name ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Host Address:</span> <span class="font-medium text-gray-900">{{ $host_address ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Destination Address:</span> <span class="font-medium text-gray-900">{{ $destination_address ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Destination Phone:</span> <span class="font-medium text-gray-900">{{ $destination_phone ?: '—' }}</span></div>
                            <div><span class="text-gray-500">Migration Observations:</span> <span class="font-medium text-gray-900">{{ $remarks ?: '—' }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($step === 1 && $showMrzReviewModal)
            <div
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
                x-data="{ timer: null }"
                x-init="timer = setTimeout(() => $wire.confirmMrzReviewAndContinue(), 1000)"
            >
                <div class="w-full max-w-3xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Confirm MRZ Details</h3>
                            <p class="text-sm text-gray-500">
                                Extracted passport data. Continuing automatically to Traveler Details.
                            </p>
                        </div>

                        <button
                            type="button"
                            x-on:click="clearTimeout(timer)"
                            wire:click="closeMrzReviewModal"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Close
                        </button>
                    </div>

                    <div class="p-6">
                        <div class="grid gap-4 md:grid-cols-2 text-sm">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <div class="text-xs uppercase tracking-wide text-gray-500">Surname</div>
                                <div class="mt-1 text-lg font-semibold text-gray-900">{{ $passport_mrz_result['surname'] ?? '—' }}</div>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <div class="text-xs uppercase tracking-wide text-gray-500">Given Names</div>
                                <div class="mt-1 text-lg font-semibold text-gray-900">{{ $passport_mrz_result['given_names'] ?? '—' }}</div>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <div class="text-xs uppercase tracking-wide text-gray-500">Passport Number</div>
                                <div class="mt-1 text-lg font-semibold text-gray-900">{{ $passport_mrz_result['passport_number'] ?? '—' }}</div>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <div class="text-xs uppercase tracking-wide text-gray-500">Nationality Code</div>
                                <div class="mt-1 text-lg font-semibold text-gray-900">{{ $passport_mrz_result['nationality_code'] ?? '—' }}</div>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <div class="text-xs uppercase tracking-wide text-gray-500">Date of Birth</div>
                                <div class="mt-1 text-lg font-semibold text-gray-900">{{ $passport_mrz_result['date_of_birth'] ?? '—' }}</div>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <div class="text-xs uppercase tracking-wide text-gray-500">Passport Expiry</div>
                                <div class="mt-1 text-lg font-semibold text-gray-900">{{ $passport_mrz_result['passport_expiry_date'] ?? '—' }}</div>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 md:col-span-2">
                                <div class="text-xs uppercase tracking-wide text-gray-500">Sex</div>
                                <div class="mt-1 text-lg font-semibold text-gray-900">{{ $passport_mrz_result['sex'] ?? '—' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4">
                        <button
                            type="button"
                            x-on:click="clearTimeout(timer)"
                            wire:click="closeMrzReviewModal"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Stay Here
                        </button>

                        <button
                            type="button"
                            x-on:click="clearTimeout(timer)"
                            wire:click="confirmMrzReviewAndContinue"
                            class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
                        >
                            Continue Now
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between gap-3">
            <div>
                @if ($step > 1)
                    <button
                        type="button"
                        wire:click="previousStep"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700"
                    >
                        Previous
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-3">
                @if ($step < 5)
                    <button
                        type="button"
                        wire:click="nextStep"
                        class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white"
                    >
                        Next Step
                    </button>
                @else
                    <button
                        type="submit"
                        class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white"
                    >
                        Save Application
                    </button>
                @endif
            </div>
        </div>
    </form>
</div>
