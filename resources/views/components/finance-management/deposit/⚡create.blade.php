<?php

use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Enums\DepositStatusEnum;
use App\Livewire\Forms\DepositForm;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use AuthorizesAdministratorPermissions;

    public DepositForm $form;

    public string $userSearch = '';

    public string $walletSearch = '';

    #[On('panels.administrator.finance-management.deposit.create.assign-data')]
    public function assignData(): void
    {
        $this->authorizePermission('finance-management.deposit.create');

        $this->form->setDefaults();
        $this->userSearch = '';
        $this->walletSearch = '';
        $this->resetValidation();
        unset($this->users, $this->wallets);

        Flux::modal('finance-management.deposit.create')->show();
    }

    public function updated(string $property): void
    {
        if ($property === 'form.user_id') {
            $this->form->wallet_id = '';
            $this->walletSearch = '';
            unset($this->wallets);
        }

        if (in_array($property, ['userSearch', 'walletSearch'], true)) {
            unset($this->users, $this->wallets);
        }
    }

    #[Computed]
    public function users(): Collection
    {
        return $this->form->userOptions($this->userSearch)
            ->map(fn ($user) => (object) [
                'id' => $user->id,
                'label' => trim($user->full_name.' — '.($user->email ?: $user->username ?: '#'.$user->id)),
            ]);
    }

    #[Computed]
    public function wallets(): Collection
    {
        return $this->form->walletOptions($this->walletSearch)
            ->map(fn ($wallet) => (object) [
                'id' => $wallet->id,
                'label' => $this->form->walletOptionLabel($wallet),
            ]);
    }

    public function save(): void
    {
        $this->authorizePermission('finance-management.deposit.create');

        $this->form->store();

        $this->form->setDefaults();
        $this->userSearch = '';
        $this->walletSearch = '';
        unset($this->users, $this->wallets);

        $this->dispatch('panels.administrator.finance-management.deposit.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.deposit_created'));
    }
};
?>

@php
    $depositMethods = \App\Support\PaymentGateways::depositMethodOptions();
    $decimals = $form->decimals();
@endphp

<flux:modal name="finance-management.deposit.create" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.create') }} {{ __('general.deposit') }}</flux:heading>
        <flux:subheading>{{ __('general.deposits') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:select wire:model.live="form.user_id" variant="combobox" :filter="false" label="{{ __('general.user') }}">
            <x-slot name="input">
                <flux:select.input wire:model.live.debounce.300ms="userSearch" placeholder="{{ __('general.search') }}..." />
            </x-slot>

            @foreach ($this->users as $userOption)
                <flux:select.option value="{{ $userOption->id }}" wire:key="deposit-create-user-{{ $userOption->id }}">
                    {{ $userOption->label }}
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="form.wallet_id" variant="combobox" :filter="false" label="{{ __('general.wallet') }}" :disabled="blank($form->user_id)">
            <x-slot name="input">
                <flux:select.input wire:model.live.debounce.300ms="walletSearch" placeholder="{{ __('general.search') }}..." />
            </x-slot>

            @foreach ($this->wallets as $walletOption)
                <flux:select.option value="{{ $walletOption->id }}" wire:key="deposit-create-wallet-{{ $walletOption->id }}">
                    {{ $walletOption->label }}
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:field>
            <flux:label>{{ __('general.amount') }}</flux:label>
            <flux:description>{{ __('general.amount_decimals_hint', ['decimals' => $decimals]) }}</flux:description>
            <flux:input wire:model="form.amount" dir="ltr" x-mask:dynamic="{{ $form->moneyMaskExpression() }}" />
            <flux:error name="form.amount" />
        </flux:field>

        <div class="grid gap-3 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('general.fee') }}</flux:label>
                <flux:input wire:model="form.fee" dir="ltr" x-mask:dynamic="{{ $form->moneyMaskExpression() }}" />
                <flux:error name="form.fee" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('general.tax') }}</flux:label>
                <flux:input wire:model="form.tax" dir="ltr" x-mask:dynamic="{{ $form->moneyMaskExpression() }}" />
                <flux:error name="form.tax" />
            </flux:field>
        </div>

        <flux:select wire:model="form.method" variant="listbox" searchable label="{{ __('general.method') }}">
            @foreach ($depositMethods as $methodKey => $methodLabel)
                <flux:select.option value="{{ $methodKey }}">{{ __($methodLabel) }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model="form.tracking_code" label="{{ __('general.tracking_code') }}" dir="ltr" />

        <flux:select wire:model="form.status" variant="listbox" searchable label="{{ __('general.status') }}">
            @foreach (DepositStatusEnum::cases() as $statusOption)
                <flux:select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:textarea wire:model="form.admin_note" label="{{ __('general.admin_note') }}" rows="3" />

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
