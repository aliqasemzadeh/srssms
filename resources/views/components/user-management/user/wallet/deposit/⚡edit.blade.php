<?php

use App\Enums\DepositStatusEnum;
use App\Livewire\Forms\DepositForm;
use App\Models\Finance\Deposit;
use App\Models\Finance\Wallet;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public User $user;

    public Wallet $wallet;

    public DepositForm $form;

    public function mount(User $user, Wallet $wallet): void
    {
        abort_unless($wallet->user_id === $user->id, 404);

        $this->user = $user;
        $this->wallet = $wallet->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);
    }

    #[On('panels.administrator.user-management.user.wallet.deposit.edit.assign-data')]
    public function assignData(int $deposit): void
    {
        $model = Deposit::query()
            ->where('wallet_id', $this->wallet->id)
            ->findOrFail($deposit);

        $this->form->setModel($model);
        $this->resetValidation();

        Flux::modal('user-management.user.wallet.deposit.edit')->show();
    }

    public function save(): void
    {
        $this->form->user_id = (string) $this->user->id;
        $this->form->wallet_id = (string) $this->wallet->id;
        $this->form->update();

        $this->dispatch('panels.administrator.user-management.user.wallet.deposit.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.deposit_updated'));
    }
};
?>

@php
    $depositMethods = \App\Support\PaymentGateways::depositMethodOptions();
    $decimals = $form->decimals();
    $currencySymbol = $wallet->currency?->symbol ?? '';
@endphp

<flux:modal name="user-management.user.wallet.deposit.edit" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.edit') }} {{ __('general.deposit') }}</flux:heading>
        <flux:subheading>
            <span dir="ltr">{{ $currencySymbol }}</span> — {{ $user->full_name }}
        </flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
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

        <flux:button type="submit" variant="primary" color="orange" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
