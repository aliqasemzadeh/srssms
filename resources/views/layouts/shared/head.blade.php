@php
    $settings = $generalSettings ?? rescue(fn () => app(\App\Settings\GeneralSettings::class), report: false);
    $siteName = $settings?->site_name ?: config('app.name');
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? $siteName }}</title>

    @if (filled($settings?->site_description))
        <meta name="description" content="{{ $settings->site_description }}">
    @endif

    @if (filled($settings?->site_favicon))
        <link rel="icon" href="{{ asset('storage/'.$settings->site_favicon) }}" type="image/x-icon">
        <link rel="shortcut icon" href="{{ asset('storage/'.$settings->site_favicon) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/'.$settings->site_favicon) }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @fluxAppearance
</head>
