<flux:sidebar.header>
    <flux:sidebar.brand
        href="#"
        logo="https://fluxui.dev/img/demo/logo.png"
        logo:dark="https://fluxui.dev/img/demo/dark-mode-logo.png"
        name="Acme Inc."
    />
    <flux:sidebar.collapse class="lg:hidden" />
</flux:sidebar.header>
<flux:sidebar.search placeholder="Search..." />
<flux:sidebar.nav>
    <flux:sidebar.item icon="home" href="{{ route('panels.user.dashboard.index') }}" :current="request()->routeIs('panels.user.dashboard.index')">{{ __('general.dashboard') }}</flux:sidebar.item>
    <flux:sidebar.item icon="inbox" badge="12" href="#">{{ __('general.inbox') }}</flux:sidebar.item>
    <flux:sidebar.item icon="document-text" href="#">{{ __('general.documents') }}</flux:sidebar.item>
    <flux:sidebar.item icon="calendar" href="#">{{ __('general.calendar') }}</flux:sidebar.item>
    <flux:sidebar.group expandable heading="{{ __('general.favorites') }}" class="grid">
        <flux:sidebar.item href="#">{{ __('general.marketing_site') }}</flux:sidebar.item>
        <flux:sidebar.item href="#">{{ __('general.android_app') }}</flux:sidebar.item>
        <flux:sidebar.item href="#">{{ __('general.brand_guidelines') }}</flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
