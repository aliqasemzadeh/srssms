<flux:sidebar.header>
    @include('layouts.shared.brand', ['href' => route('panels.administrator.dashboard.index')])
    <flux:sidebar.collapse class="lg:hidden" />
</flux:sidebar.header>

<x-sidebar-menu-search>
    <flux:sidebar.nav>
        <div x-show="showItem($el)" x-cloak>
            <flux:sidebar.item icon="home" href="{{ route('panels.administrator.dashboard.index') }}" :current="request()->routeIs('panels.administrator.dashboard.index')" wire:navigate>{{ __('general.dashboard') }}</flux:sidebar.item>
        </div>

        @canany([
            'user-management.user.view',
            'user-management.role.view',
            'user-management.permission.view',
        ])
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
                    @can('user-management.user.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.user-management.user.index') }}" :current="request()->routeIs('panels.administrator.user-management.user.*')" wire:navigate>{{ __('general.users') }}</flux:sidebar.item>
                        </div>
                    @endcan
                    @can('user-management.role.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.user-management.role.index') }}" :current="request()->routeIs('panels.administrator.user-management.role.index')" wire:navigate>{{ __('general.roles') }}</flux:sidebar.item>
                        </div>
                    @endcan
                    @can('user-management.permission.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.user-management.permission.index') }}" :current="request()->routeIs('panels.administrator.user-management.permission.index')" wire:navigate>{{ __('general.permissions') }}</flux:sidebar.item>
                        </div>
                    @endcan
                </flux:sidebar.group>
            </div>
        @endcanany

        @canany([
            'finance-management.currency.view',
            'finance-management.wallet.view',
            'finance-management.transaction.view',
            'finance-management.deposit.view',
            'finance-management.withdrawal.view',
            'finance-management.payment.view',
        ])
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
                    @can('finance-management.currency.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.finance-management.currency.index') }}" :current="request()->routeIs('panels.administrator.finance-management.currency.index')" wire:navigate>{{ __('general.currencies') }}</flux:sidebar.item>
                        </div>
                    @endcan
                    @can('finance-management.wallet.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.finance-management.wallet.index') }}" :current="request()->routeIs('panels.administrator.finance-management.wallet.index')" wire:navigate>{{ __('general.wallets') }}</flux:sidebar.item>
                        </div>
                    @endcan
                    @can('finance-management.transaction.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.finance-management.transaction.index') }}" :current="request()->routeIs('panels.administrator.finance-management.transaction.index')" wire:navigate>{{ __('general.transactions') }}</flux:sidebar.item>
                        </div>
                    @endcan
                    @can('finance-management.deposit.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.finance-management.deposit.index') }}" :current="request()->routeIs('panels.administrator.finance-management.deposit.index')" wire:navigate>{{ __('general.deposits') }}</flux:sidebar.item>
                        </div>
                    @endcan
                    @can('finance-management.withdrawal.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.finance-management.withdrawal.index') }}" :current="request()->routeIs('panels.administrator.finance-management.withdrawal.index')" wire:navigate>{{ __('general.withdrawals') }}</flux:sidebar.item>
                        </div>
                    @endcan
                    @can('finance-management.payment.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.finance-management.payment.index') }}" :current="request()->routeIs('panels.administrator.finance-management.payment.*')" wire:navigate>{{ __('general.payments') }}</flux:sidebar.item>
                        </div>
                    @endcan
                </flux:sidebar.group>
            </div>
        @endcanany

        @canany([
            'sms-management.provider.view',
            'sms-management.gateway.view',
            'sms-management.message.view',
            'sms-management.setting.view',
        ])
            <div
                data-sidebar-menu-group
                data-sidebar-menu-heading="{{ __('general.sms_management') }}"
                x-show="matches($el)"
                x-cloak
            >
                <flux:sidebar.group
                    expandable
                    icon="message-square-text"
                    heading="{{ __('general.sms_management') }}"
                    class="grid"
                    :expanded="request()->routeIs('panels.administrator.sms-management.*')"
                >
                    @can('sms-management.provider.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.sms-management.provider.index') }}" :current="request()->routeIs('panels.administrator.sms-management.provider.*')" wire:navigate>{{ __('general.providers') }}</flux:sidebar.item>
                        </div>
                    @endcan
                    @can('sms-management.gateway.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.sms-management.gateway.index') }}" :current="request()->routeIs('panels.administrator.sms-management.gateway.*')" wire:navigate>{{ __('general.sms_gateways') }}</flux:sidebar.item>
                        </div>
                    @endcan
                    @can('sms-management.message.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.sms-management.message.index') }}" :current="request()->routeIs('panels.administrator.sms-management.message.*')" wire:navigate>{{ __('general.sms_messages') }}</flux:sidebar.item>
                        </div>
                    @endcan
                    @can('sms-management.setting.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.sms-management.setting.index') }}" :current="request()->routeIs('panels.administrator.sms-management.setting.*')" wire:navigate>{{ __('general.sms_settings') }}</flux:sidebar.item>
                        </div>
                    @endcan
                </flux:sidebar.group>
            </div>
        @endcanany

        @canany([
            'support-system.ticket.view',
        ])
            <div
                data-sidebar-menu-group
                data-sidebar-menu-heading="{{ __('general.support_system') }}"
                x-show="matches($el)"
                x-cloak
            >
                <flux:sidebar.group
                    expandable
                    icon="life-buoy"
                    heading="{{ __('general.support_system') }}"
                    class="grid"
                    :expanded="request()->routeIs('panels.administrator.support-system.*')"
                >
                    @can('support-system.ticket.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.support-system.ticket.new') }}" :current="request()->routeIs('panels.administrator.support-system.ticket.new')" wire:navigate>{{ __('general.new_tickets') }}</flux:sidebar.item>
                        </div>
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.support-system.ticket.index') }}" :current="request()->routeIs('panels.administrator.support-system.ticket.index') || request()->routeIs('panels.administrator.support-system.ticket.view')" wire:navigate>{{ __('general.all_tickets') }}</flux:sidebar.item>
                        </div>
                    @endcan
                </flux:sidebar.group>
            </div>
        @endcanany

        @canany([
            'system-management.setting.view',
            'system-management.function.view',
            'system-management.backup.view',
            'system-management.log.view',
        ])
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
                    @can('system-management.setting.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.system-management.setting.index') }}" :current="request()->routeIs('panels.administrator.system-management.setting.index')" wire:navigate>{{ __('general.settings') }}</flux:sidebar.item>
                        </div>
                    @endcan
                    @can('system-management.function.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.system-management.function.index') }}" :current="request()->routeIs('panels.administrator.system-management.function.index')" wire:navigate>{{ __('general.functions') }}</flux:sidebar.item>
                        </div>
                    @endcan
                    @can('system-management.backup.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('panels.administrator.system-management.backup.index') }}" :current="request()->routeIs('panels.administrator.system-management.backup.index')" wire:navigate>{{ __('general.backups') }}</flux:sidebar.item>
                        </div>
                    @endcan
                    @can('system-management.log.view')
                        <div x-show="showItem($el)" x-cloak>
                            <flux:sidebar.item href="{{ route('log-viewer.index') }}" target="_blank">{{ __('general.log_viewer') }}</flux:sidebar.item>
                        </div>
                    @endcan
                </flux:sidebar.group>
            </div>
        @endcanany
    </flux:sidebar.nav>
</x-sidebar-menu-search>
