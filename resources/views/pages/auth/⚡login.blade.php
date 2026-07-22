<?php

use App\Livewire\Forms\Auth\LoginForm;
use Flux\Flux;
use Livewire\Component;

new #[\Livewire\Attributes\Layout('layouts.auth')] class extends Component
{
    public LoginForm $form;

    public function login()
    {
        $this->form->authenticate();

        Flux::toast(__('general.login_success'));

        return redirect()->intended('/');
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.login') }} - {{ config('app.name') }}</x-slot>

    <form wire:submit="login" class="space-y-4">
        <flux:tab.group>
            <flux:tabs variant="segmented" wire:model.live="form.loginType" class="w-full">
                <flux:tab name="mobile" class="flex-1 justify-center cursor-pointer">
                    {{ __('general.mobile') }}
                </flux:tab>
                <flux:tab name="email" class="flex-1 justify-center cursor-pointer">
                    {{ __('general.email') }}
                </flux:tab>
                <flux:tab name="username" class="flex-1 justify-center cursor-pointer">
                    {{ __('general.username') }}
                </flux:tab>
            </flux:tabs>

            <flux:tab.panel name="mobile">
                <flux:field>
                    <flux:label>{{ __('general.mobile') }}</flux:label>
                    <flux:input type="text" wire:model="form.mobile" icon="phone" placeholder="09123456789" />
                    <flux:error name="form.mobile" />
                </flux:field>
            </flux:tab.panel>

            <flux:tab.panel name="email">
                <flux:field>
                    <flux:label>{{ __('general.email') }}</flux:label>
                    <flux:input type="email" wire:model="form.email" icon="envelope" placeholder="your@email.com" />
                    <flux:error name="form.email" />
                </flux:field>
            </flux:tab.panel>

            <flux:tab.panel name="username">
                <flux:field>
                    <flux:label>{{ __('general.username') }}</flux:label>
                    <flux:input type="text" wire:model="form.username" icon="user" placeholder="username" />
                    <flux:error name="form.username" />
                </flux:field>
            </flux:tab.panel>
        </flux:tab.group>

        <flux:field>
            <flux:label>{{ __('general.password') }}</flux:label>
            <flux:input type="password" wire:model="form.password" icon="lock" placeholder="••••••••" viewable />
            <flux:error name="form.password" />
        </flux:field>

        <flux:checkbox wire:model="form.remember" label="{{ __('general.remember_me') }}" />

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('general.login') }}
        </flux:button>
    </form>

    <div class="mt-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
        {{ __('general.no_account') }}
        <flux:link href="{{ route('register') }}" wire:navigate class="font-medium">
            {{ __('general.register') }}
        </flux:link>
    </div>
</div>
