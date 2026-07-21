<flux:dropdown position="top" align="start" class="{{ $class ?? 'max-lg:hidden' }}">
    <flux:sidebar.profile :name="auth()->user()->full_name" />

    <flux:menu>
        <flux:menu.heading>{{ auth()->user()->full_name }}</flux:menu.heading>

        @if (auth()->user()->mobile)
            <flux:menu.heading>{{ auth()->user()->mobile }}</flux:menu.heading>
        @endif

        @if (auth()->user()->email)
            <flux:menu.heading>{{ auth()->user()->email }}</flux:menu.heading>
        @endif

        <flux:menu.separator />

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <flux:menu.item type="submit" icon="arrow-right-start-on-rectangle">
                {{ __('actions.log_out') }}
            </flux:menu.item>
        </form>
    </flux:menu>
</flux:dropdown>
