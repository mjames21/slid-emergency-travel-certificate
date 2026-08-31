@php
    $navigationLinkClass = fn (bool $active) => ($active
        ? 'bg-emerald-800 text-white'
        : 'text-emerald-100 hover:bg-emerald-900 hover:text-white')
        .' block rounded-md px-4 py-3 text-sm font-medium';
@endphp

<div class="px-4 text-xs font-semibold uppercase tracking-wider text-emerald-300">HQ Review</div>

<a href="{{ route('hq.emergency-travel-certificates.index') }}"
   class="{{ $navigationLinkClass(request()->routeIs('hq.emergency-travel-certificates.*') || request()->routeIs('dashboard')) }}">
    ETC Applications
</a>

@if ($user?->hasStaffTitle('etc_issuer'))
    <a href="{{ route('etc.apply') }}"
       class="{{ $navigationLinkClass(request()->routeIs('etc.apply')) }}">
        Office Application
    </a>
@endif

@if ($user?->hasStaffTitle('system_administrator'))
    <div class="px-4 pt-4 text-xs font-semibold uppercase tracking-wider text-emerald-300">Administration</div>

    <a href="{{ route('admin.staff.users.index') }}"
       class="{{ $navigationLinkClass(request()->routeIs('admin.staff.users.*')) }}">
        Staff Users
    </a>
@endif

<a href="{{ route('profile.show') }}"
   class="{{ $navigationLinkClass(request()->routeIs('profile.show')) }}">
    Profile And MFA
</a>
