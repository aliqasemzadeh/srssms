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
    <flux:sidebar.item icon="home" href="{{ route('panels.administrator.dashboard.index') }}" current>{{ __('general.dashboard') }}</flux:sidebar.item>
    <flux:sidebar.group expandable heading="{{ __('general.user_management') }}" class="grid">
        <flux:sidebar.item href="{{ route('panels.administrator.user-management.user.index') }}">{{ __('general.users') }}</flux:sidebar.item>
        <flux:sidebar.item href="{{ route('panels.administrator.user-management.role.index') }}">{{ __('general.roles') }}</flux:sidebar.item>
        <flux:sidebar.item href="{{ route('panels.administrator.user-management.permission.index') }}">{{ __('general.permissions') }}</flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
