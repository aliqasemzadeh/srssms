<flux:sidebar.header>
    @include('layouts.shared.brand', ['href' => route('panels.administrator.dashboard.index')])
    <flux:sidebar.collapse class="lg:hidden" />
</flux:sidebar.header>

<x-sidebar-menu-search>
    <flux:sidebar.nav>
        <div x-show="showItem($el)" x-cloak>
            <flux:sidebar.item icon="home" href="{{ route('panels.administrator.dashboard.index') }}" :current="request()->routeIs('panels.administrator.dashboard.index')">{{ __('general.dashboard') }}</flux:sidebar.item>
        </div>

        <div
            data-sidebar-menu-group
            data-sidebar-menu-heading="{{ __('general.user_management') }}"
            x-show="matches($el)"
            x-cloak
        >
            <flux:sidebar.group
                expandable
                icon="users"
                heading="{{ __('general.user_management') }}"
                class="grid"
                :expanded="request()->routeIs('panels.administrator.user-management.*')"
            >
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.administrator.user-management.user.index') }}" :current="request()->routeIs('panels.administrator.user-management.user.*')" wire:navigate>{{ __('general.users') }}</flux:sidebar.item>
                </div>
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.administrator.user-management.role.index') }}" :current="request()->routeIs('panels.administrator.user-management.role.index')" wire:navigate>{{ __('general.roles') }}</flux:sidebar.item>
                </div>
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.administrator.user-management.permission.index') }}" :current="request()->routeIs('panels.administrator.user-management.permission.index')" wire:navigate>{{ __('general.permissions') }}</flux:sidebar.item>
                </div>
            </flux:sidebar.group>
        </div>

        <div
            data-sidebar-menu-group
            data-sidebar-menu-heading="{{ __('general.finance_management') }}"
            x-show="matches($el)"
            x-cloak
        >
            <flux:sidebar.group
                expandable
                icon="banknote"
                heading="{{ __('general.finance_management') }}"
                class="grid"
                :expanded="request()->routeIs('panels.administrator.finance-management.*')"
            >
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.administrator.finance-management.currency.index') }}" :current="request()->routeIs('panels.administrator.finance-management.currency.index')" wire:navigate>{{ __('general.currencies') }}</flux:sidebar.item>
                </div>
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.administrator.finance-management.wallet.index') }}" :current="request()->routeIs('panels.administrator.finance-management.wallet.index')" wire:navigate>{{ __('general.wallets') }}</flux:sidebar.item>
                </div>
            </flux:sidebar.group>
        </div>

        <div
            data-sidebar-menu-group
            data-sidebar-menu-heading="{{ __('general.system_management') }}"
            x-show="matches($el)"
            x-cloak
        >
            <flux:sidebar.group
                expandable
                icon="settings"
                heading="{{ __('general.system_management') }}"
                class="grid"
                :expanded="request()->routeIs('panels.administrator.system-management.*')"
            >
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.administrator.system-management.setting.index') }}" :current="request()->routeIs('panels.administrator.system-management.setting.index')" wire:navigate>{{ __('general.settings') }}</flux:sidebar.item>
                </div>
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.administrator.system-management.function.index') }}" :current="request()->routeIs('panels.administrator.system-management.function.index')" wire:navigate>{{ __('general.functions') }}</flux:sidebar.item>
                </div>
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.administrator.system-management.backup.index') }}" :current="request()->routeIs('panels.administrator.system-management.backup.index')" wire:navigate>{{ __('general.backups') }}</flux:sidebar.item>
                </div>
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('log-viewer.index') }}" target="_blank">{{ __('general.log_viewer') }}</flux:sidebar.item>
                </div>
            </flux:sidebar.group>
        </div>
    </flux:sidebar.nav>
</x-sidebar-menu-search>
