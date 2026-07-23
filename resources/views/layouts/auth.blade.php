@php
    $settings = $generalSettings ?? rescue(fn () => app(\App\Settings\GeneralSettings::class), report: false);
    $siteName = $settings?->site_name ?: config('app.name');
    $shortName = $settings?->site_short_name ?: strtoupper(mb_substr($siteName, 0, 3));
    $logo = filled($settings?->site_logo) ? asset('storage/'.$settings->site_logo) : null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ __('general.direction') }}">
@include('layouts.shared.head')
<body class="min-h-screen bg-zinc-50 dark:bg-zinc-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="mb-8 flex flex-col items-center gap-3 text-center">
            @if ($logo)
                <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-14 w-auto object-contain" />
            @else
                <div class="flex size-14 items-center justify-center rounded-xl bg-teal-600 text-lg font-bold tracking-wide text-white">
                    {{ $shortName }}
                </div>
            @endif

            <div class="space-y-1">
                <flux:heading size="xl">{{ $title ?? $siteName }}</flux:heading>
                @if (filled($settings?->site_description) && empty($title))
                    <flux:subheading>{{ $settings->site_description }}</flux:subheading>
                @endif
            </div>
        </div>

        <flux:card>
            {{ $slot }}
        </flux:card>

        <div class="mt-4 flex justify-center">
            @include('layouts.shared.theme')
        </div>
    </div>

    @include('layouts.shared.foot')
</body>
</html>
