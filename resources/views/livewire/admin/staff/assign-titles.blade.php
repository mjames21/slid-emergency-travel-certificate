<div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-xl bg-white p-5 shadow">
        <h2 class="text-lg font-semibold text-gray-900">Users</h2>
        <div class="mt-4 space-y-3">
            @foreach ($users as $user)
                <div class="rounded-lg border border-gray-200 px-4 py-3">
                    <div class="font-medium text-gray-900">{{ $user->name }}</div>
                    <div class="text-sm text-gray-500">{{ $user->staffTitles->pluck('code')->join(', ') ?: 'No titles' }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow">
        <h2 class="text-lg font-semibold text-gray-900">Staff Titles</h2>
        <div class="mt-4 space-y-3">
            @foreach ($staffTitles as $title)
                <div class="rounded-lg border border-gray-200 px-4 py-3">
                    <div class="font-medium text-gray-900">{{ $title->name }}</div>
                    <div class="text-sm text-gray-500">{{ $title->code }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
