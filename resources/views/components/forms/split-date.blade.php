@props([
    'label',
    'yearModel',
    'monthModel',
    'dayModel',
    'help' => '',
    'required' => false,
    'yearPlaceholder' => 'YYYY',
    'monthPlaceholder' => 'MM',
    'dayPlaceholder' => 'DD',
    'prefix' => 'date',
])

<div x-data class="md:col-span-2">
    <label class="mb-1 block text-sm font-medium text-gray-700">
        {{ $label }} @if($required)<span class="text-red-600">*</span>@endif
    </label>

    <div class="grid grid-cols-3 gap-2">
        <input
            x-ref="{{ $prefix }}Year"
            type="text"
            inputmode="numeric"
            maxlength="4"
            wire:model.defer="{{ $yearModel }}"
            placeholder="{{ $yearPlaceholder }}"
            class="w-full rounded-lg border-gray-300 shadow-sm"
            @input="if ($el.value.length >= 4) $refs.{{ $prefix }}Month.focus()"
        >

        <input
            x-ref="{{ $prefix }}Month"
            type="text"
            inputmode="numeric"
            maxlength="2"
            wire:model.defer="{{ $monthModel }}"
            placeholder="{{ $monthPlaceholder }}"
            class="w-full rounded-lg border-gray-300 shadow-sm"
            @input="if ($el.value.length >= 2) $refs.{{ $prefix }}Day.focus()"
            @keydown.backspace="if ($el.value.length === 0) $refs.{{ $prefix }}Year.focus()"
        >

        <input
            x-ref="{{ $prefix }}Day"
            type="text"
            inputmode="numeric"
            maxlength="2"
            wire:model.defer="{{ $dayModel }}"
            placeholder="{{ $dayPlaceholder }}"
            class="w-full rounded-lg border-gray-300 shadow-sm"
            @keydown.backspace="if ($el.value.length === 0) $refs.{{ $prefix }}Month.focus()"
        >
    </div>

    @if ($help)
        <p class="mt-1 text-xs text-gray-500">{{ $help }}</p>
    @endif

    @error($yearModel)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error($monthModel)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error($dayModel)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>