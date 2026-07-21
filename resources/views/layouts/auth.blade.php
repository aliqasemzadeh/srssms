<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ __('general.direction') }}">
@include('layouts.shared.head')
<body class="min-h-screen bg-zinc-50 dark:bg-zinc-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="flex justify-center mb-8">
            <flux:heading size="2xl">{{ $title ?? config('app.name') }}</flux:heading>
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
