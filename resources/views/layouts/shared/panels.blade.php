<flux:sidebar.nav>
    <flux:sidebar.item icon="layout-dashboard" href="{{ route('panels.administrator.dashboard.index') }}" :current="request()->is('panels/administrator*')">{{ __('general.administrator_panel') }}</flux:sidebar.item>
    <flux:sidebar.item icon="user" href="{{ route('panels.user.dashboard.index') }}" :current="request()->is('panels/user*')">{{ __('general.user_panel') }}</flux:sidebar.item>
</flux:sidebar.nav>
