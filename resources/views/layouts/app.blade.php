<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ __('general.direction') }}">
@include('layouts.shared.head')
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
<flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">

    @include('layouts.panels.user')
    @include('layouts.panels.administrator')

    <flux:sidebar.spacer />

    @include('layouts.shared.menus')
    @include('layouts.shared.user', ['class' => 'max-lg:hidden'])
</flux:sidebar>
<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
    <flux:spacer />

    @include('layouts.shared.theme')
    @include('layouts.shared.user', ['class' => 'lg:hidden'])
</flux:header>
<flux:main>
    {{ $slot }}
</flux:main>
@include('layouts.shared.foot')
</body>
</html>
