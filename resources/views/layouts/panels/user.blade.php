<flux:sidebar.header>
    @include('layouts.shared.brand', ['href' => route('panels.user.dashboard.index')])
    <flux:sidebar.collapse class="lg:hidden" />
</flux:sidebar.header>

<x-sidebar-menu-search>
    <flux:sidebar.nav>
        <div x-show="showItem($el)" x-cloak>
            <flux:sidebar.item icon="home" href="{{ route('panels.user.dashboard.index') }}" :current="request()->routeIs('panels.user.dashboard.index')" wire:navigate>{{ __('general.dashboard') }}</flux:sidebar.item>
        </div>

        <div
            data-sidebar-menu-group
            data-sidebar-menu-heading="{{ __('general.phonebook') }}"
            x-show="matches($el)"
            x-cloak
        >
            <flux:sidebar.group
                expandable
                icon="book-user"
                heading="{{ __('general.phonebook') }}"
                class="grid"
                :expanded="request()->routeIs('panels.user.phonebook.*')"
            >
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.user.phonebook.index') }}" :current="request()->routeIs('panels.user.phonebook.index') || request()->routeIs('panels.user.phonebook.view')" wire:navigate>{{ __('general.contacts') }}</flux:sidebar.item>
                </div>
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.user.phonebook.group.index') }}" :current="request()->routeIs('panels.user.phonebook.group.*')" wire:navigate>{{ __('general.phonebook_groups') }}</flux:sidebar.item>
                </div>
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.user.phonebook.tag.index') }}" :current="request()->routeIs('panels.user.phonebook.tag.*')" wire:navigate>{{ __('general.phonebook_tags') }}</flux:sidebar.item>
                </div>
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.user.phonebook.note.index') }}" :current="request()->routeIs('panels.user.phonebook.note.*')" wire:navigate>{{ __('general.notes') }}</flux:sidebar.item>
                </div>
            </flux:sidebar.group>
        </div>

        <div
            data-sidebar-menu-group
            data-sidebar-menu-heading="{{ __('general.sms') }}"
            x-show="matches($el)"
            x-cloak
        >
            <flux:sidebar.group
                expandable
                icon="message-square"
                heading="{{ __('general.sms') }}"
                class="grid"
                :expanded="request()->routeIs('panels.user.sms.*')"
            >
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.user.sms.message.index') }}" :current="request()->routeIs('panels.user.sms.message.*')" wire:navigate>{{ __('general.sms_messages') }}</flux:sidebar.item>
                </div>
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.user.sms.send') }}" :current="request()->routeIs('panels.user.sms.send') || request()->routeIs('panels.user.sms.preview')" wire:navigate>{{ __('general.send_sms') }}</flux:sidebar.item>
                </div>
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.user.sms.token.index') }}" :current="request()->routeIs('panels.user.sms.token.index')" wire:navigate>{{ __('general.sms_tokens') }}</flux:sidebar.item>
                </div>
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.user.sms.token.logs') }}" :current="request()->routeIs('panels.user.sms.token.logs')" wire:navigate>{{ __('general.sms_token_logs') }}</flux:sidebar.item>
                </div>
                <div x-show="showItem($el)" x-cloak>
                    <flux:sidebar.item href="{{ route('panels.user.sms.token.doc') }}" :current="request()->routeIs('panels.user.sms.token.doc')" wire:navigate>{{ __('general.sms_api_docs') }}</flux:sidebar.item>
                </div>
            </flux:sidebar.group>
        </div>

        <div x-show="showItem($el)" x-cloak>
            <flux:sidebar.item icon="wallet" href="{{ route('panels.user.wallet.index') }}" :current="request()->routeIs('panels.user.wallet.*')" wire:navigate>{{ __('general.wallet') }}</flux:sidebar.item>
        </div>
        <div x-show="showItem($el)" x-cloak>
            <flux:sidebar.item icon="settings" href="{{ route('panels.user.setting.index') }}" :current="request()->routeIs('panels.user.setting.index')" wire:navigate>{{ __('general.settings') }}</flux:sidebar.item>
        </div>
    </flux:sidebar.nav>
</x-sidebar-menu-search>
