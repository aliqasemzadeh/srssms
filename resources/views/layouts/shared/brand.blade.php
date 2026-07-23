@php
    $settings = $generalSettings ?? rescue(fn () => app(\App\Settings\GeneralSettings::class), report: false);
    $name = $settings?->site_name ?: config('app.name');
    $shortName = $settings?->site_short_name ?: strtoupper(mb_substr($name, 0, 3));
    $logo = filled($settings?->site_logo) ? asset('storage/'.$settings->site_logo) : null;
@endphp

@if ($logo)
    <flux:sidebar.brand
        href="{{ $href }}"
        :logo="$logo"
        :name="$name"
        :alt="$name"
    />
@else
    <flux:sidebar.brand href="{{ $href }}" :name="$name" :alt="$name">
        <x-slot name="logo" class="size-6 rounded-sm bg-teal-600 text-white text-[10px] font-bold tracking-wide">
            {{ $shortName }}
        </x-slot>
    </flux:sidebar.brand>
@endif
