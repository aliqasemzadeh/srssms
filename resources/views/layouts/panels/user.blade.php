<flux:sidebar.header>
    <flux:sidebar.brand
        href="{{ route('panels.user.dashboard.index') }}"
        logo="https://fluxui.dev/img/demo/logo.png"
        logo:dark="https://fluxui.dev/img/demo/dark-mode-logo.png"
        name="Acme Inc."
    />
    <flux:sidebar.collapse class="lg:hidden" />
</flux:sidebar.header>
<flux:sidebar.search placeholder="{{ __('general.search') }}..." />
<flux:sidebar.nav>
    <flux:sidebar.item icon="home" href="{{ route('panels.user.dashboard.index') }}" :current="request()->routeIs('panels.user.dashboard.index')">{{ __('general.dashboard') }}</flux:sidebar.item>
</flux:sidebar.nav>
