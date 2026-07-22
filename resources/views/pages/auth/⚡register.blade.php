<?php

use App\Livewire\Forms\Auth\RegisterForm;
use Flux\Flux;
use Livewire\Component;

new #[\Livewire\Attributes\Layout('layouts.auth')] class extends Component
{
    public RegisterForm $form;

    public function register()
    {
        $this->form->register();

        Flux::toast(__('general.register_success'));

        return redirect()->intended('/');
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.register') }} - {{ config('app.name') }}</x-slot>

    <form wire:submit="register" class="space-y-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('general.first_name') }}</flux:label>
                <flux:input type="text" wire:model="form.first_name" icon="user" />
                <flux:error name="form.first_name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('general.last_name') }}</flux:label>
                <flux:input type="text" wire:model="form.last_name" icon="user" />
                <flux:error name="form.last_name" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>{{ __('general.mobile') }}</flux:label>
            <flux:input type="text" wire:model="form.mobile" icon="phone" placeholder="09123456789" />
            <flux:error name="form.mobile" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('general.email') }}</flux:label>
            <flux:input type="email" wire:model="form.email" icon="envelope" placeholder="your@email.com" />
            <flux:error name="form.email" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('general.username') }}</flux:label>
            <flux:input type="text" wire:model="form.username" icon="user" placeholder="username" />
            <flux:error name="form.username" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('general.password') }}</flux:label>
            <flux:input type="password" wire:model="form.password" icon="lock" placeholder="••••••••" viewable />
            <flux:error name="form.password" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('general.password_confirmation') }}</flux:label>
            <flux:input type="password" wire:model="form.password_confirmation" icon="lock" placeholder="••••••••" viewable />
            <flux:error name="form.password_confirmation" />
        </flux:field>

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('general.register') }}
        </flux:button>
    </form>

    <div class="mt-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
        {{ __('general.have_account') }}
        <flux:link href="{{ route('login') }}" wire:navigate class="font-medium">
            {{ __('general.login') }}
        </flux:link>
    </div>
</div>
