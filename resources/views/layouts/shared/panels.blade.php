<flux:sidebar.nav>
    @canany(app(\App\Services\Permission\PermissionCatalog::class)->names())
        <flux:sidebar.item icon="layout-dashboard" href="{{ route('panels.administrator.dashboard.index') }}" :current="request()->is('panels/administrator*')">{{ __('general.administrator_panel') }}</flux:sidebar.item>
    @else
        @role(\App\Services\Permission\PermissionCatalog::ROLE_NAME)
            <flux:sidebar.item icon="layout-dashboard" href="{{ route('panels.administrator.dashboard.index') }}" :current="request()->is('panels/administrator*')">{{ __('general.administrator_panel') }}</flux:sidebar.item>
        @endrole
    @endcanany
    <flux:sidebar.item icon="user" href="{{ route('panels.user.dashboard.index') }}" :current="request()->is('panels/user*')">{{ __('general.user_panel') }}</flux:sidebar.item>
</flux:sidebar.nav>
