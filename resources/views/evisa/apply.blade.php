<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Apply for Sierra Leone Emergency Travel Certificate | SLID LEAPS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-950 antialiased">
    @php
        $stepOneFields = [
            'applicant_category', 'regional_category', 'identity_document_type',
            'passport_biodata_image', 'applicant_photo', 'mrz_line_1', 'mrz_line_2',
        ];

        $stepTwoFields = [
            'surname', 'given_names', 'nationality', 'nationality_code', 'passport_number',
            'passport_expiry_year', 'passport_expiry_month', 'passport_expiry_day',
            'sex', 'date_of_birth_year', 'date_of_birth_month', 'date_of_birth_day',
            'place_of_birth', 'country_of_birth', 'marital_status',
        ];

        $stepThreeFields = [
            'applicant_address', 'occupation', 'email', 'phone',
            'guardian_name', 'guardian_relationship', 'guardian_address', 'guardian_phone', 'guardian_sex',
        ];

        $stepFourFields = [
            'destination_country', 'destination_address', 'purpose_of_visit',
            'flight_carrier', 'flight_number', 'flight_details', 'remarks',
        ];

        $stepFiveFields = ['applicant_certification'];
        $etcPurposeOptions = [
            'Medical',
            'Return home',
            'Family emergency',
            'Lost passport',
            'Official travel',
        ];

        $errorStep = 1;

        if ($errors->hasAny($stepTwoFields)) {
            $errorStep = 2;
        } elseif ($errors->hasAny($stepThreeFields)) {
            $errorStep = 3;
        } elseif ($errors->hasAny($stepFourFields)) {
            $errorStep = 4;
        } elseif ($errors->hasAny($stepFiveFields)) {
            $errorStep = 5;
        }

    @endphp

    <main class="mx-auto max-w-6xl px-4 py-2 sm:px-6 lg:px-8">
        <div class="mb-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/slid-logo.png') }}" alt="SLID" class="h-8 w-8 object-contain">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wide text-emerald-800">Sierra Leone Immigration Department</div>
                    <div class="text-xs font-bold text-gray-950">Emergency Travel Certificate</div>
                </div>
            </a>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('home') }}" class="rounded-md border border-gray-300 bg-white px-3 py-1 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Home</a>
            </div>
        </div>

        <div class="overflow-hidden border border-gray-300 bg-white shadow-sm">
            <div class="border-b border-emerald-900 bg-emerald-950 px-4 py-3 text-white sm:px-5">
                <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-200">Official emergency travel certificate application</div>
                <h1 class="mt-1 text-xl font-bold tracking-tight">Sierra Leone Emergency Travel Certificate Application</h1>
                <p class="mt-1 max-w-4xl text-sm leading-5 text-emerald-50">
                    Complete the SLID ETC form online: evidence, personal details, contact or guardian details, destination, declaration, and WanGov/GovPay payment.
                </p>
                <div class="mt-1.5 text-[11px] font-bold uppercase tracking-wide text-emerald-200">
                    No account required. A tracking code and status link are issued after submission.
                </div>
            </div>

            <div class="grid lg:grid-cols-[230px_1fr]">
                <aside class="border-b border-gray-200 bg-gray-50 p-3 lg:border-b-0 lg:border-r">
                    <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Application sections</div>
                    <div class="mt-2 space-y-1.5">
                        @foreach ([
                            1 => 'Evidence',
                            2 => 'Personal',
                            3 => 'Contact',
                            4 => 'Destination',
                            5 => 'Declaration',
                        ] as $stepNumber => $stepLabel)
                            <div
                                data-progress-step="{{ $stepNumber }}"
                                class="{{ $stepNumber === 1 ? 'border-emerald-600 bg-emerald-700 text-white' : 'border-gray-200 bg-white text-gray-600' }} border px-3 py-1.5 text-sm font-semibold"
                            >
                                {{ $stepNumber }}. {{ $stepLabel }}
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 border border-gray-200 bg-white p-3">
                        <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Draft</div>
                        <div id="draft-status" class="mt-2 text-sm text-gray-700">Not saved yet</div>
                        <button type="button" id="clear-draft-button" class="mt-3 text-sm font-semibold text-emerald-800 hover:text-emerald-950">
                            Clear saved draft
                        </button>
                    </div>
                </aside>

                <div class="px-5 py-3 sm:px-6">
                    <form id="etc-application-form" method="POST" action="{{ route('etc.store') }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf

                        <input type="hidden" name="point_of_entry" value="{{ old('point_of_entry', 'Emergency Travel Certificate Desk') }}">
                        <input type="hidden" name="period_of_stay_days" value="{{ old('period_of_stay_days', 30) }}">
                        <input type="hidden" name="arrival_date" value="{{ old('arrival_date', now()->toDateString()) }}">

                        @if (isset($errors) && $errors->any())
                            <div data-validation-summary class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                                <div class="font-bold">Please correct the highlighted fields.</div>
                                <ul class="mt-2 list-disc space-y-1 pl-5">
                                    @foreach ($errors->getMessages() as $field => $messages)
                                        @foreach ($messages as $error)
                                            <li data-error-for="{{ $field }}">{{ $error }}</li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <section data-step="1" class="space-y-4">
                            <div class="border-b border-gray-200 pb-2">
                                <div class="text-xs font-bold uppercase tracking-wide text-emerald-700">Step 1 of 5</div>
                                <h2 class="mt-1 text-xl font-bold">Application Type and Evidence</h2>
                                <p class="mt-1 text-sm text-gray-600">Select the form category, then upload identity evidence.</p>
                            </div>

                            <div class="grid gap-3 md:grid-cols-2">
                                <div class="rounded-md border border-gray-200 bg-white p-3">
                                    <label class="text-sm font-bold text-gray-900">Applicant Type <span class="text-red-600">*</span></label>
                                    <div class="mt-2 grid grid-cols-2 gap-2">
                                        <label class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm font-semibold">
                                            <input type="radio" name="applicant_category" value="adult" @checked(old('applicant_category', 'adult') === 'adult') class="text-emerald-700 focus:ring-emerald-600">
                                            Adult
                                        </label>
                                        <label class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm font-semibold">
                                            <input type="radio" name="applicant_category" value="child" @checked(old('applicant_category') === 'child') class="text-emerald-700 focus:ring-emerald-600">
                                            Child
                                        </label>
                                    </div>
                                    @error('applicant_category') <div class="mt-2 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div class="rounded-md border border-gray-200 bg-white p-3">
                                    <label class="text-sm font-bold text-gray-900">Regional Category <span class="text-red-600">*</span></label>
                                    <div class="mt-2 grid grid-cols-2 gap-2">
                                        <label class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm font-semibold">
                                            <input type="radio" name="regional_category" value="ecowas" @checked(old('regional_category') === 'ecowas') class="text-emerald-700 focus:ring-emerald-600">
                                            ECOWAS
                                        </label>
                                        <label class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm font-semibold">
                                            <input type="radio" name="regional_category" value="non_ecowas" @checked(old('regional_category', 'non_ecowas') === 'non_ecowas') class="text-emerald-700 focus:ring-emerald-600">
                                            Non-ECOWAS
                                        </label>
                                    </div>
                                    @error('regional_category') <div class="mt-2 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="rounded-md border border-emerald-200 bg-emerald-50 p-3">
                                <div class="grid gap-3 md:grid-cols-2">
                                    <div>
                                        <label class="text-sm font-bold text-gray-900">Identity Document Type <span class="text-red-600">*</span></label>
                                        <select name="identity_document_type" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                            <option value="passport" @selected(old('identity_document_type', 'passport') === 'passport')>Passport</option>
                                            <option value="nin" @selected(old('identity_document_type') === 'nin')>National Identification Number</option>
                                            <option value="other" @selected(old('identity_document_type') === 'other')>Other supporting identity document</option>
                                        </select>
                                        @error('identity_document_type') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-bold text-gray-900">Passport / NIN evidence <span class="text-red-600">*</span></label>
                                        <input id="passport_biodata_image" name="passport_biodata_image" type="file" accept="image/*" class="mt-1 block w-full rounded-md border border-gray-300 bg-white text-sm text-gray-700 file:mr-4 file:border-0 file:bg-emerald-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                                        @error('passport_biodata_image') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-bold text-gray-900">Applicant Photo <span class="text-red-600">*</span></label>
                                        <input name="applicant_photo" type="file" accept="image/*" class="mt-1 block w-full rounded-md border border-gray-300 bg-white text-sm text-gray-700 file:mr-4 file:border-0 file:bg-emerald-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                                        @error('applicant_photo') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="rounded-md border border-emerald-200 bg-white p-3">
                                        <div class="text-sm font-bold text-gray-900">Passport MRZ reader</div>
                                        <p class="mt-1 text-xs text-gray-600">Use passport MRZ to pre-fill details when available.</p>
                                        <div id="passport-read-message" class="mt-3 hidden rounded-md border px-3 py-2 text-sm"></div>
                                    </div>
                                </div>

                                <details class="mt-3 rounded-md border border-emerald-200 bg-white p-3 shadow-sm">
                                    <summary class="cursor-pointer text-sm font-semibold text-gray-900">Image unclear? Type MRZ lines instead</summary>
                                    <p class="mt-2 text-xs text-gray-600">Copy the two machine-readable lines at the bottom of the passport page. Use &lt; exactly as printed.</p>
                                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                                        <label class="block">
                                            <span class="text-xs font-semibold text-gray-700">MRZ Line 1</span>
                                            <input name="mrz_line_1" value="{{ old('mrz_line_1') }}" maxlength="64" autocomplete="off" placeholder="P&lt;SLEJAMES&lt;&lt;MOHAMED&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;" class="mt-1 w-full rounded-md border-gray-300 font-mono text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold text-gray-700">MRZ Line 2</span>
                                            <input name="mrz_line_2" value="{{ old('mrz_line_2') }}" maxlength="64" autocomplete="off" placeholder="SLR0923770SLE8604217M2903124&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;06" class="mt-1 w-full rounded-md border-gray-300 font-mono text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        </label>
                                    </div>
                                </details>

                                <div class="mt-3 flex flex-wrap gap-3">
                                    <button id="read-passport-button" type="button" class="rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-800">
                                        Read passport and continue
                                    </button>
                                    <button type="button" data-next-step="2" class="rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                        Save and continue manually
                                    </button>
                                </div>
                            </div>
                        </section>

                        <section data-step="2" class="hidden space-y-5">
                            <div class="border-b border-gray-200 pb-3">
                                <div class="text-xs font-bold uppercase tracking-wide text-emerald-700">Step 2 of 5</div>
                                <h2 class="mt-1 text-xl font-bold">Personal Details</h2>
                                <p class="mt-1 text-sm text-gray-600">These fields follow the personal details section of the current ETC paper form.</p>
                            </div>

                            <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Surname <span class="text-red-600">*</span></label>
                                        <input name="surname" value="{{ old('surname') }}" placeholder="Surname" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        @error('surname') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Given Names <span class="text-red-600">*</span></label>
                                        <input name="given_names" value="{{ old('given_names') }}" placeholder="Given names" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        @error('given_names') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Nationality <span class="text-red-600">*</span></label>
                                        <select id="nationality_select" name="nationality" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                            <option value="">Select nationality</option>
                                            @foreach ($nationalities as $nationality)
                                                <option value="{{ $nationality->name }}" data-code="{{ $nationality->code }}" @selected(old('nationality') === $nationality->name)>
                                                    {{ $nationality->name }} - {{ $nationality->code }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('nationality') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Nationality Code</label>
                                        <input id="nationality_code" name="nationality_code" value="{{ old('nationality_code') }}" readonly placeholder="Auto-filled from nationality" class="mt-1 w-full rounded-md border-gray-300 bg-gray-50 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        @error('nationality_code') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Passport / NIN No. <span class="text-red-600">*</span></label>
                                        <input name="passport_number" value="{{ old('passport_number') }}" placeholder="Passport or NIN number" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        @error('passport_number') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Sex</label>
                                        <select name="sex" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                            <option value="">Select sex</option>
                                            <option value="M" @selected(old('sex') === 'M')>Male</option>
                                            <option value="F" @selected(old('sex') === 'F')>Female</option>
                                            <option value="X" @selected(old('sex') === 'X')>Unspecified / X</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Date of Birth <span class="text-red-600">*</span></label>
                                        <div class="mt-1 grid grid-cols-3 gap-2">
                                            <input name="date_of_birth_year" value="{{ old('date_of_birth_year') }}" inputmode="numeric" maxlength="4" placeholder="YYYY" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                            <input name="date_of_birth_month" value="{{ old('date_of_birth_month') }}" inputmode="numeric" maxlength="2" placeholder="MM" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                            <input name="date_of_birth_day" value="{{ old('date_of_birth_day') }}" inputmode="numeric" maxlength="2" placeholder="DD" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        </div>
                                        @error('date_of_birth_year') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Passport Expiry Date, if available</label>
                                        <div class="mt-1 grid grid-cols-3 gap-2">
                                            <input name="passport_expiry_year" value="{{ old('passport_expiry_year') }}" inputmode="numeric" maxlength="4" placeholder="YYYY" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                            <input name="passport_expiry_month" value="{{ old('passport_expiry_month') }}" inputmode="numeric" maxlength="2" placeholder="MM" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                            <input name="passport_expiry_day" value="{{ old('passport_expiry_day') }}" inputmode="numeric" maxlength="2" placeholder="DD" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        </div>
                                        @error('passport_expiry_year') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Place of Birth <span class="text-red-600">*</span></label>
                                        <input name="place_of_birth" value="{{ old('place_of_birth') }}" placeholder="Town, city, or district" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        @error('place_of_birth') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Country of Birth</label>
                                        <input name="country_of_birth" value="{{ old('country_of_birth') }}" type="text" list="country-list" placeholder="Search country" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        @error('country_of_birth') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Marital Status</label>
                                        <select name="marital_status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                            <option value="">Select status</option>
                                            @foreach (['single' => 'Single', 'married' => 'Married', 'divorced' => 'Divorced', 'widowed' => 'Widowed', 'separated' => 'Separated', 'other' => 'Other'] as $value => $label)
                                                <option value="{{ $value }}" @selected(old('marital_status') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('marital_status') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <datalist id="country-list">
                                @foreach ($countries as $country)
                                    <option value="{{ $country->name }}"></option>
                                @endforeach
                            </datalist>

                            <div class="flex justify-between border-t border-gray-200 pt-4">
                                <button type="button" data-next-step="1" class="rounded-md border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700">Back</button>
                                <button type="button" data-next-step="3" class="rounded-md bg-emerald-700 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-800">Save and continue</button>
                            </div>
                        </section>

                        <section data-step="3" class="hidden space-y-5">
                            <div class="border-b border-gray-200 pb-3">
                                <div class="text-xs font-bold uppercase tracking-wide text-emerald-700">Step 3 of 5</div>
                                <h2 class="mt-1 text-xl font-bold">Address, Contact, and Guardian</h2>
                                <p class="mt-1 text-sm text-gray-600">Applicant address and phone match the paper form. Guardian details are required for child applicants.</p>
                            </div>

                            <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="md:col-span-2">
                                        <label class="text-sm font-semibold text-gray-700">Address <span class="text-red-600">*</span></label>
                                        <textarea name="applicant_address" rows="2" placeholder="Current address" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">{{ old('applicant_address') }}</textarea>
                                        @error('applicant_address') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Occupation <span class="text-red-600">*</span></label>
                                        <input name="occupation" value="{{ old('occupation') }}" placeholder="Occupation" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        @error('occupation') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Phone Number <span class="text-red-600">*</span></label>
                                        <input name="phone" value="{{ old('phone') }}" placeholder="Phone number" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        @error('phone') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Email for Decision Notice <span class="text-red-600">*</span></label>
                                        <input name="email" value="{{ old('email') }}" type="email" placeholder="Certificate decision will be sent here" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        @error('email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Country of Residence</label>
                                        <input name="country_of_residence" value="{{ old('country_of_residence') }}" type="text" list="country-list" placeholder="Search country" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        @error('country_of_residence') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div id="guardian-section" class="{{ old('applicant_category', 'adult') === 'child' ? '' : 'hidden ' }}rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-base font-bold text-gray-950">Parent / Guardian Details</h3>
                                        <p class="mt-1 text-sm text-gray-600">Required for applicants under sixteen (16).</p>
                                    </div>
                                    <span class="rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700">Child applicants</span>
                                </div>

                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Name</label>
                                        <input data-guardian-field name="guardian_name" value="{{ old('guardian_name') }}" placeholder="Parent or guardian name" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" @disabled(old('applicant_category', 'adult') !== 'child')>
                                        @error('guardian_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Relationship to Applicant</label>
                                        <input data-guardian-field name="guardian_relationship" value="{{ old('guardian_relationship') }}" placeholder="Father, mother, guardian" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" @disabled(old('applicant_category', 'adult') !== 'child')>
                                        @error('guardian_relationship') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="text-sm font-semibold text-gray-700">Address</label>
                                        <textarea data-guardian-field name="guardian_address" rows="2" placeholder="Guardian address" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" @disabled(old('applicant_category', 'adult') !== 'child')>{{ old('guardian_address') }}</textarea>
                                        @error('guardian_address') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Telephone</label>
                                        <input data-guardian-field name="guardian_phone" value="{{ old('guardian_phone') }}" placeholder="Guardian telephone" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" @disabled(old('applicant_category', 'adult') !== 'child')>
                                        @error('guardian_phone') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Sex</label>
                                        <select data-guardian-field name="guardian_sex" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" @disabled(old('applicant_category', 'adult') !== 'child')>
                                            <option value="">Select</option>
                                            <option value="M" @selected(old('guardian_sex') === 'M')>Male</option>
                                            <option value="F" @selected(old('guardian_sex') === 'F')>Female</option>
                                        </select>
                                        @error('guardian_sex') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between border-t border-gray-200 pt-4">
                                <button type="button" data-next-step="2" class="rounded-md border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700">Back</button>
                                <button type="button" data-next-step="4" class="rounded-md bg-emerald-700 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-800">Save and continue</button>
                            </div>
                        </section>

                        <section data-step="4" class="hidden space-y-5">
                            <div class="border-b border-gray-200 pb-3">
                                <div class="text-xs font-bold uppercase tracking-wide text-emerald-700">Step 4 of 5</div>
                                <h2 class="mt-1 text-xl font-bold">Destination and Purpose of Traveling</h2>
                                <p class="mt-1 text-sm text-gray-600">This mirrors the destination and purpose fields on the current ETC paper form.</p>
                            </div>

                            <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Destination <span class="text-red-600">*</span></label>
                                        <input name="destination_country" value="{{ old('destination_country') }}" type="text" list="country-list" placeholder="Destination country" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        @error('destination_country') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Carrier / Flight, if known</label>
                                        <input name="flight_carrier" value="{{ old('flight_carrier') }}" type="text" list="etc-flight-carrier-list" placeholder="Airline or carrier" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        <datalist id="etc-flight-carrier-list">
                                            @foreach ($flightCarriers as $carrier)
                                                <option value="{{ $carrier['name'] }}">{{ $carrier['code'] ?? '' }}</option>
                                            @endforeach
                                        </datalist>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="text-sm font-semibold text-gray-700">Destination Address or Contact, if known</label>
                                        <textarea name="destination_address" rows="2" placeholder="Address, contact, or location overseas" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">{{ old('destination_address') }}</textarea>
                                        @error('destination_address') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="text-sm font-semibold text-gray-700">Purpose of Traveling <span class="text-red-600">*</span></label>
                                        <input name="purpose_of_visit" value="{{ old('purpose_of_visit') }}" type="text" list="purpose-list" placeholder="Medical, return home, family emergency, lost passport, official travel" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        <datalist id="purpose-list">
                                            @foreach ($etcPurposeOptions as $purpose)
                                                <option value="{{ $purpose }}"></option>
                                            @endforeach
                                        </datalist>
                                        @error('purpose_of_visit') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Flight Number, if known</label>
                                        <input name="flight_number" value="{{ old('flight_number') }}" placeholder="Flight number" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">Travel Details / Remarks</label>
                                        <input name="flight_details" value="{{ old('flight_details') }}" placeholder="Route or emergency travel details" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="text-sm font-semibold text-gray-700">Additional Remarks</label>
                                        <textarea name="remarks" rows="2" placeholder="Additional information for immigration review" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">{{ old('remarks') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between border-t border-gray-200 pt-4">
                                <button type="button" data-next-step="3" class="rounded-md border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700">Back</button>
                                <button type="button" data-next-step="5" class="rounded-md bg-emerald-700 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-800">Save and continue</button>
                            </div>
                        </section>

                        <section data-step="5" class="hidden space-y-5">
                            <div class="border-b border-gray-200 pb-3">
                                <div class="text-xs font-bold uppercase tracking-wide text-emerald-700">Step 5 of 5</div>
                                <h2 class="mt-1 text-xl font-bold">Declaration and Payment</h2>
                                <p class="mt-1 text-sm text-gray-600">Submit the official ETC request. Payment details are handled after submission and recorded against the application.</p>
                            </div>

                            <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                                <h3 class="text-base font-bold text-gray-950">Official use and online payment</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-700">
                                    ETC applications use online WanGov/GovPay payment after submission. One authorized ETC Issuer approves and issues the certificate after payment is confirmed.
                                </p>
                                <div class="mt-4 grid gap-3 md:grid-cols-3">
                                    <div class="rounded-md border border-gray-200 bg-gray-50 p-3 text-sm">
                                        <div class="font-semibold text-gray-900">Official Use Only</div>
                                        <div class="mt-1 text-gray-600">Issuer approval record</div>
                                    </div>
                                    <div class="rounded-md border border-gray-200 bg-gray-50 p-3 text-sm">
                                        <div class="font-semibold text-gray-900">Online Payment</div>
                                        <div class="mt-1 text-gray-600">WanGov/GovPay fee confirmation</div>
                                    </div>
                                    <div class="rounded-md border border-gray-200 bg-gray-50 p-3 text-sm">
                                        <div class="font-semibold text-gray-900">ETC Issuer</div>
                                        <div class="mt-1 text-gray-600">Final issue trail</div>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-md border border-gray-300 bg-gray-50 p-4">
                                <h3 class="text-base font-bold text-gray-950">Applicant certification</h3>
                                <label class="mt-3 flex gap-3 text-sm leading-6 text-gray-800">
                                    <input name="applicant_certification" value="1" type="checkbox" @checked(old('applicant_certification')) class="mt-1 rounded border-gray-300 text-emerald-700 focus:ring-emerald-600">
                                    <span>I certify that I have reviewed this Emergency Travel Certificate application and that the information provided is true and complete.</span>
                                </label>
                                @error('applicant_certification') <div class="mt-2 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="rounded-md border border-emerald-200 bg-emerald-50 p-4">
                                <h3 class="text-base font-bold text-emerald-950">Payment step</h3>
                                <p class="mt-2 text-sm leading-6 text-emerald-900">
                                    After submission, the system creates a tracking code and opens the WanGov/GovPay fee payment page. The ETC Issuer reviews, approves, and issues the certificate after online payment is confirmed.
                                </p>
                            </div>

                            <div class="flex justify-between border-t border-gray-200 pt-4">
                                <button type="button" data-next-step="4" class="rounded-md border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700">Back</button>
                                <button type="submit" class="rounded-md bg-emerald-700 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-800">Submit and continue to payment</button>
                            </div>
                        </section>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        (() => {
            const form = document.getElementById('etc-application-form');
            const sections = Array.from(document.querySelectorAll('[data-step]'));
            const progress = Array.from(document.querySelectorAll('[data-progress-step]'));
            const message = document.getElementById('passport-read-message');
            const readButton = document.getElementById('read-passport-button');
            const fileInput = document.getElementById('passport_biodata_image');
            const nationalitySelect = document.getElementById('nationality_select');
            const nationalityCodeInput = document.getElementById('nationality_code');
            const draftStatus = document.getElementById('draft-status');
            const clearDraftButton = document.getElementById('clear-draft-button');
            const validationSummary = document.querySelector('[data-validation-summary]');
            const applicantCategoryInputs = Array.from(form.querySelectorAll('[name="applicant_category"]'));
            const guardianSection = document.getElementById('guardian-section');
            const guardianFields = Array.from(guardianSection?.querySelectorAll('[data-guardian-field]') || []);
            const hasErrors = @json($errors->any());
            const errorStep = @json($errorStep);
            const draftKey = 'slid:etc:application:draft:v2';
            let saveTimer = null;

            const showStep = (step) => {
                const currentStep = Number(step);

                sections.forEach((section) => section.classList.toggle('hidden', section.dataset.step !== String(currentStep)));
                progress.forEach((item) => {
                    const active = item.dataset.progressStep === String(currentStep);
                    item.className = active
                        ? 'border border-emerald-600 bg-emerald-700 px-3 py-1.5 text-sm font-semibold text-white'
                        : 'border border-gray-200 bg-white px-3 py-1.5 text-sm font-semibold text-gray-600';
                });
            };

            const updateValidationSummary = () => {
                if (!validationSummary) return;

                if (!validationSummary.querySelector('[data-error-for]')) {
                    validationSummary.classList.add('hidden');
                }
            };

            const clearErrorsForField = (field) => {
                if (!field?.name) return;

                document.querySelectorAll('[data-error-for]').forEach((error) => {
                    if (error.dataset.errorFor === field.name) {
                        error.remove();
                    }
                });

                field.closest('div')?.querySelectorAll('.text-xs.text-red-600').forEach((error) => error.remove());
                updateValidationSummary();
            };

            const isChildApplicant = () => form.querySelector('[name="applicant_category"]:checked')?.value === 'child';

            const updateGuardianVisibility = () => {
                const childApplicant = isChildApplicant();
                guardianSection?.classList.toggle('hidden', !childApplicant);
                guardianFields.forEach((field) => {
                    field.disabled = !childApplicant;

                    if (!childApplicant) {
                        clearErrorsForField(field);
                    }
                });
            };

            const draftFields = () => Array.from(form.querySelectorAll('input, select, textarea'))
                .filter((field) => field.name && field.type !== 'file' && field.name !== '_token');

            const updateDraftStatus = (savedAt = null) => {
                if (!draftStatus) return;
                draftStatus.textContent = savedAt
                    ? `Saved on this device at ${new Date(savedAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`
                    : 'Not saved yet';
            };

            const saveDraft = () => {
                const values = {};
                draftFields().forEach((field) => {
                    if (field.type === 'radio') {
                        if (field.checked) values[field.name] = field.value;
                        return;
                    }
                    if (field.type === 'checkbox') {
                        values[field.name] = field.checked ? field.value : '';
                        return;
                    }
                    values[field.name] = field.value;
                });

                const savedAt = new Date().toISOString();
                localStorage.setItem(draftKey, JSON.stringify({ savedAt, values }));
                updateDraftStatus(savedAt);
            };

            const scheduleDraftSave = () => {
                clearTimeout(saveTimer);
                saveTimer = setTimeout(saveDraft, 350);
            };

            const restoreDraft = () => {
                if (hasErrors) return;

                try {
                    const draft = JSON.parse(localStorage.getItem(draftKey) || 'null');
                    if (!draft?.values) return;

                    draftFields().forEach((field) => {
                        if (!Object.prototype.hasOwnProperty.call(draft.values, field.name)) return;

                        if (field.type === 'radio') {
                            field.checked = field.value === draft.values[field.name];
                            return;
                        }

                        if (field.type === 'checkbox') {
                            field.checked = draft.values[field.name] !== '';
                            return;
                        }

                        field.value = draft.values[field.name] || '';
                    });

                    updateDraftStatus(draft.savedAt);
                } catch (_) {
                    localStorage.removeItem(draftKey);
                }
            };

            const setMessage = (ok, text) => {
                if (!message) return;
                message.classList.remove('hidden', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-800', 'border-amber-200', 'bg-amber-50', 'text-amber-800');
                message.classList.add(...(ok
                    ? ['border-emerald-200', 'bg-emerald-50', 'text-emerald-800']
                    : ['border-amber-200', 'bg-amber-50', 'text-amber-800']));
                message.textContent = text;
            };

            const fillField = (name, value) => {
                if (!value) return;
                const field = form.querySelector(`[name="${name}"]`);
                if (field) field.value = value;
            };

            const setDateParts = (prefix, isoDate) => {
                if (!isoDate || !/^\d{4}-\d{2}-\d{2}$/.test(isoDate)) return;
                const [year, month, day] = isoDate.split('-');
                fillField(`${prefix}_year`, year);
                fillField(`${prefix}_month`, month);
                fillField(`${prefix}_day`, day);
            };

            const selectNationalityByCode = (code) => {
                if (!code || !nationalitySelect) return;
                const normalizedCode = String(code).toUpperCase();
                const option = Array.from(nationalitySelect.options).find((item) => item.dataset.code === normalizedCode);

                if (option) {
                    nationalitySelect.value = option.value;
                }

                if (nationalityCodeInput) {
                    nationalityCodeInput.value = normalizedCode;
                }
            };

            nationalitySelect?.addEventListener('change', () => {
                const option = nationalitySelect.options[nationalitySelect.selectedIndex];
                if (nationalityCodeInput) nationalityCodeInput.value = option?.dataset?.code || '';
                clearErrorsForField(nationalitySelect);
                clearErrorsForField(nationalityCodeInput);
                saveDraft();
            });

            applicantCategoryInputs.forEach((input) => {
                input.addEventListener('change', () => {
                    clearErrorsForField(input);
                    updateGuardianVisibility();
                    saveDraft();
                });
            });

            document.querySelectorAll('[data-next-step]').forEach((button) => {
                button.addEventListener('click', () => {
                    saveDraft();
                    showStep(button.dataset.nextStep);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });

            form?.addEventListener('input', (event) => {
                clearErrorsForField(event.target);
                scheduleDraftSave();
            });
            form?.addEventListener('change', (event) => {
                clearErrorsForField(event.target);
                scheduleDraftSave();
            });
            form?.addEventListener('submit', () => localStorage.removeItem(draftKey));

            clearDraftButton?.addEventListener('click', () => {
                localStorage.removeItem(draftKey);
                updateDraftStatus();
            });

            readButton?.addEventListener('click', async () => {
                const formData = new FormData();
                const file = fileInput?.files?.[0];
                const line1 = form.querySelector('[name="mrz_line_1"]')?.value || '';
                const line2 = form.querySelector('[name="mrz_line_2"]')?.value || '';

                if (file) formData.append('passport_biodata_image', file);
                if (line1) formData.append('mrz_line_1', line1);
                if (line2) formData.append('mrz_line_2', line2);
                formData.append('_token', @json(csrf_token()));

                readButton.disabled = true;
                readButton.textContent = 'Reading passport...';

                try {
                    const response = await fetch(@json(route('etc.read-passport')), {
                        method: 'POST',
                        body: formData,
                        headers: { Accept: 'application/json' },
                    });

                    const result = await response.json();
                    setMessage(Boolean(result.ok), result.message || 'Passport reader completed.');

                    if (result.ok && result.parsed) {
                        fillField('surname', result.parsed.surname);
                        fillField('given_names', result.parsed.given_names);
                        fillField('passport_number', result.parsed.passport_number);
                        fillField('sex', result.parsed.sex);
                        selectNationalityByCode(result.parsed.nationality_code);
                        setDateParts('date_of_birth', result.parsed.date_of_birth);
                        setDateParts('passport_expiry', result.parsed.passport_expiry_date);
                        saveDraft();
                        showStep(2);
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                } catch (_) {
                    setMessage(false, 'Passport reader is unavailable. Continue manually.');
                } finally {
                    readButton.disabled = false;
                    readButton.textContent = 'Read passport and continue';
                }
            });

            restoreDraft();
            updateGuardianVisibility();
            showStep(hasErrors ? errorStep : 1);
        })();
    </script>
</body>
</html>
