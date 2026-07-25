<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ __('general.page_direction') }}">
@include('layouts.shared.head')
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
<flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">

    <div
        class="relative min-h-0 flex-1"
        x-data="{ loading: false }"
        x-on:livewire:navigate.window="
            const next = ($event.detail.url?.pathname || new URL($event.detail.url, window.location.origin).pathname);
            const curAdmin = location.pathname.startsWith('/panels/administrator');
            const nextAdmin = next.startsWith('/panels/administrator');
            const curUser = location.pathname.startsWith('/panels/user');
            const nextUser = next.startsWith('/panels/user');
            if ((curAdmin && nextUser) || (curUser && nextAdmin)) loading = true;
        "
        x-on:livewire:navigated.window="loading = false"
    >
        <div
            x-show="loading"
            x-cloak
            class="absolute inset-0 z-10 flex items-center justify-center bg-zinc-50/80 dark:bg-zinc-900/80"
        >
            <div class="size-6 animate-spin rounded-full border-2 border-zinc-300 border-t-zinc-600 dark:border-zinc-600 dark:border-t-zinc-200"></div>
        </div>

        <div :class="loading && 'opacity-40 pointer-events-none'">
            @if(request()->is('panels/administrator*'))
                @include('layouts.panels.administrator')
            @elseif(request()->is('panels/user*'))
                @include('layouts.panels.user')
            @endif
        </div>
    </div>

    <flux:sidebar.spacer />

    @include('layouts.shared.panels')
    @include('layouts.shared.user', ['class' => 'max-lg:hidden'])
    @include('layouts.shared.theme')
</flux:sidebar>
<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
    <flux:spacer />
    @include('layouts.shared.user', ['class' => 'lg:hidden'])
</flux:header>
<livewire:impersonation-banner :key="'impersonation-banner'" />
<flux:main>
    {{ $slot }}
</flux:main>
@include('layouts.shared.foot')
</body>
</html>
