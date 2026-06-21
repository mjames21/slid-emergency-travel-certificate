@props([
    'label',
    'model',
    'items' => [],
    'placeholder' => '',
    'help' => '',
    'minChars' => 1,
    'showAllOnFocus' => false,
    'secondaryField' => null,
    'secondaryMetaKey' => null,
])

<div
    x-data="{
        open: false,
        query: @entangle($model).live,
        items: @js($items),
        minChars: {{ (int) $minChars }},
        showAllOnFocus: @js((bool) $showAllOnFocus),

        get filtered() {
            const q = (this.query || '').toLowerCase().trim();

            if (!q && !this.showAllOnFocus) return [];
            if (q.length > 0 && q.length < this.minChars) return [];

            const matches = !q
                ? this.items
                : this.items.filter(item => {
                    const label = (item.label || '').toLowerCase();
                    const value = (item.value || '').toLowerCase();
                    const secondary = (item.secondary || '').toLowerCase();

                    return (
                        label.startsWith(q) ||
                        value.startsWith(q) ||
                        secondary.startsWith(q) ||
                        label.includes(q) ||
                        value.includes(q) ||
                        secondary.includes(q)
                    );
                });

            return matches.slice(0, 12);
        },

        choose(item) {
            this.query = item.value;
            $wire.set('{{ $model }}', item.value);

            if ('{{ $secondaryField }}' && '{{ $secondaryMetaKey }}') {
                const metaValue = item.meta?.['{{ $secondaryMetaKey }}'] ?? '';
                $wire.set('{{ $secondaryField }}', metaValue);
            }

            this.open = false;
        },

        clearSelection() {
            this.query = '';
            $wire.set('{{ $model }}', '');

            if ('{{ $secondaryField }}') {
                $wire.set('{{ $secondaryField }}', '');
            }

            this.open = false;
        }
    }"
    @click.away="open = false"
>
    <label class="mb-1 block text-sm font-medium text-gray-700">{{ $label }}</label>

    <div class="relative">
        <input
            type="text"
            x-model="query"
            @input="open = true"
            @focus="open = showAllOnFocus || query.length >= minChars"
            autocomplete="off"
            class="w-full rounded-lg border-gray-300 pr-10 shadow-sm"
            placeholder="{{ $placeholder }}"
        >

        <div class="absolute inset-y-0 right-2 flex items-center gap-2">
            <button
                type="button"
                x-show="query"
                @click="clearSelection"
                class="text-sm text-gray-400"
            >
                ×
            </button>

            <button
                type="button"
                @click="open = !open"
                class="text-sm text-gray-500"
            >
                ▼
            </button>
        </div>

        <div
            x-show="open && filtered.length"
            x-cloak
            class="absolute z-50 mt-1 max-h-72 w-full overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg"
        >
            <template x-for="item in filtered" :key="`${item.value}-${item.secondary || ''}`">
                <button
                    type="button"
                    @click="choose(item)"
                    class="block w-full border-b border-gray-100 px-4 py-3 text-left last:border-b-0 hover:bg-gray-50"
                >
                    <div class="text-sm font-medium text-gray-900" x-text="item.label"></div>
                    <div class="text-xs text-gray-500" x-show="item.secondary" x-text="item.secondary"></div>
                </button>
            </template>
        </div>
    </div>

    @if ($help)
        <p class="mt-1 text-xs text-gray-500">{{ $help }}</p>
    @endif

    @error($model)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>