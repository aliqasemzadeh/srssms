<flux:sidebar.header>
    @include('layouts.shared.brand', ['href' => route('panels.user.dashboard.index')])
    <flux:sidebar.collapse class="lg:hidden" />
</flux:sidebar.header>

<x-sidebar-menu-search>
    <flux:sidebar.nav>
        <div x-show="showItem($el)" x-cloak>
            <flux:sidebar.item icon="home" href="{{ route('panels.user.dashboard.index') }}" :current="request()->routeIs('panels.user.dashboard.index')">{{ __('general.dashboard') }}</flux:sidebar.item>
        </div>
        <div x-show="showItem($el)" x-cloak>
            <flux:sidebar.item icon="settings" href="{{ route('panels.user.setting.index') }}" :current="request()->routeIs('panels.user.setting.index')">{{ __('general.settings') }}</flux:sidebar.item>
        </div>
    </flux:sidebar.nav>
</x-sidebar-menu-search>
