<flux:sidebar.nav>
    @php
        $user = auth()->user();
        $canAccessAdministratorPanel = $user
            && (
                $user->hasRole(\App\Services\Permission\PermissionCatalog::ROLE_NAME)
                || $user->hasAnyPermission(app(\App\Services\Permission\PermissionCatalog::class)->names())
            );
    @endphp
    @if ($canAccessAdministratorPanel)
        <flux:sidebar.item icon="layout-dashboard" href="{{ route('panels.administrator.dashboard.index') }}" :current="request()->is('panels/administrator*')">{{ __('general.administrator_panel') }}</flux:sidebar.item>
    @endif
    <flux:sidebar.item icon="user" href="{{ route('panels.user.dashboard.index') }}" :current="request()->is('panels/user*')">{{ __('general.user_panel') }}</flux:sidebar.item>
</flux:sidebar.nav>
