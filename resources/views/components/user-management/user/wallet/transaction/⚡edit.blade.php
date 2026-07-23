<?php

use App\Livewire\Forms\TransactionForm;
use App\Models\Finance\Transaction;
use App\Models\Finance\Wallet;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public User $user;

    public Wallet $wallet;

    public TransactionForm $form;

    public function mount(User $user, Wallet $wallet): void
    {
        abort_unless($wallet->user_id === $user->id, 404);

        $this->user = $user;
        $this->wallet = $wallet->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);
        $this->form->setWallet($this->wallet);
    }

    #[On('panels.administrator.user-management.user.wallet.transaction.edit.assign-data')]
    public function assignData(int $transaction): void
    {
        $model = Transaction::query()
            ->where('wallet_id', $this->wallet->id)
            ->findOrFail($transaction);

        $this->form->setModel($model);
        $this->resetValidation();

        Flux::modal('user-management.user.wallet.transaction.edit')->show();
    }

    public function save(): void
    {
        $this->form->update();

        $this->wallet->refresh()->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);

        $this->dispatch('panels.administrator.user-management.user.wallet.transaction.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.transaction_updated'));
    }
};
?>

@php
    $decimals = $form->decimals();
    $step = $form->amountStep();
    $currencySymbol = $wallet->currency?->symbol ?? '';
@endphp

<flux:modal name="user-management.user.wallet.transaction.edit" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.edit') }} {{ __('general.transaction') }}</flux:heading>
        <flux:subheading>
            <span dir="ltr">{{ $currencySymbol }}</span> — {{ $user->full_name }}
        </flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:select wire:model="form.type" variant="listbox" searchable label="{{ __('general.type') }}">
            <flux:select.option value="credit">{{ __('general.transaction_type_credit') }}</flux:select.option>
            <flux:select.option value="debit">{{ __('general.transaction_type_debit') }}</flux:select.option>
        </flux:select>

        <flux:input
            wire:model="form.amount"
            type="number"
            min="0"
            step="{{ $step }}"
            label="{{ __('general.amount') }}"
            description="{{ __('general.amount_decimals_hint', ['decimals' => $decimals]) }}"
            placeholder="0"
            dir="ltr"
            icon="coins"
        />

        <flux:textarea
            wire:model="form.description"
            label="{{ __('general.description') }}"
            description="{{ __('general.transaction_description_hint') }}"
            placeholder="{{ __('general.description') }}..."
            rows="3"
        />

        <flux:button type="submit" variant="primary" color="orange" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
