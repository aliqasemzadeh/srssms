<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ __('general.direction') }}">
@include('layouts.shared.head')
<body class="min-h-screen bg-zinc-50 dark:bg-zinc-900 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        {{ $slot }}
    </div>

    @include('layouts.shared.foot')
</body>
</html>
