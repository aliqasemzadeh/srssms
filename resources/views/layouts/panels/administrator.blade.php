<flux:sidebar.header>
    <flux:sidebar.brand
        href="{{ route('panels.administrator.dashboard.index') }}"
        logo="https://fluxui.dev/img/demo/logo.png"
        logo:dark="https://fluxui.dev/img/demo/dark-mode-logo.png"
        name="Acme Inc."
    />
    <flux:sidebar.collapse class="lg:hidden" />
</flux:sidebar.header>
<flux:sidebar.search placeholder="{{ __('general.search') }}..." />
<flux:sidebar.nav>
    <flux:sidebar.item icon="home" href="{{ route('panels.administrator.dashboard.index') }}" :current="request()->routeIs('panels.administrator.dashboard.index')">{{ __('general.dashboard') }}</flux:sidebar.item>
    <flux:sidebar.group
        expandable
        icon="users"
        heading="{{ __('general.user_management') }}"
        class="grid"
        :expanded="request()->routeIs('panels.administrator.user-management.*')"
    >
        <flux:sidebar.item href="{{ route('panels.administrator.user-management.user.index') }}" :current="request()->routeIs('panels.administrator.user-management.user.index')">{{ __('general.users') }}</flux:sidebar.item>
        <flux:sidebar.item href="{{ route('panels.administrator.user-management.role.index') }}" :current="request()->routeIs('panels.administrator.user-management.role.index')">{{ __('general.roles') }}</flux:sidebar.item>
        <flux:sidebar.item href="{{ route('panels.administrator.user-management.permission.index') }}" :current="request()->routeIs('panels.administrator.user-management.permission.index')">{{ __('general.permissions') }}</flux:sidebar.item>
    </flux:sidebar.group>
    <flux:sidebar.group
        expandable
        icon="settings"
        heading="{{ __('general.system_management') }}"
        class="grid"
        :expanded="request()->routeIs('panels.administrator.system-management.*')"
    >
        <flux:sidebar.item href="{{ route('panels.administrator.system-management.setting.index') }}" :current="request()->routeIs('panels.administrator.system-management.setting.index')">{{ __('general.settings') }}</flux:sidebar.item>
        <flux:sidebar.item href="{{ route('panels.administrator.system-management.function.index') }}" :current="request()->routeIs('panels.administrator.system-management.function.index')">{{ __('general.functions') }}</flux:sidebar.item>
        <flux:sidebar.item href="{{ route('panels.administrator.system-management.backup.index') }}" :current="request()->routeIs('panels.administrator.system-management.backup.index')">{{ __('general.backups') }}</flux:sidebar.item>
        <flux:sidebar.item href="{{ route('log-viewer.index') }}" target="_blank">{{ __('general.log_viewer') }}</flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
