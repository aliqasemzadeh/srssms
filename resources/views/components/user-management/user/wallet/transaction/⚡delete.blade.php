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

    public ?Transaction $transaction = null;

    public function mount(User $user, Wallet $wallet): void
    {
        abort_unless($wallet->user_id === $user->id, 404);

        $this->user = $user;
        $this->wallet = $wallet->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);
        $this->form->setWallet($this->wallet);
    }

    #[On('panels.administrator.user-management.user.wallet.transaction.delete.assign-data')]
    public function assignData(int $transaction): void
    {
        $this->transaction = Transaction::query()
            ->where('wallet_id', $this->wallet->id)
            ->findOrFail($transaction);

        $this->form->setModel($this->transaction);

        Flux::modal('user-management.user.wallet.transaction.delete')->show();
    }

    public function delete(): void
    {
        if (! $this->transaction) {
            return;
        }

        $this->form->delete();

        $this->transaction = null;

        $this->wallet->refresh()->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);

        $this->dispatch('panels.administrator.user-management.user.wallet.transaction.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.transaction_deleted'));
    }
};
?>

@php
    $decimals = $wallet->currency?->decimals ?? 8;
    $currencySymbol = $wallet->currency?->symbol ?? '';
@endphp

<flux:modal name="user-management.user.wallet.transaction.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>

        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($transaction)
        <flux:callout icon="arrow-left-right" variant="secondary" inline>
            <flux:callout.heading>
                {{ __('general.transaction_type_'.$transaction->type) }}
                —
                <span dir="ltr">{{ number_format((float) $transaction->amount, $decimals) }} {{ $currencySymbol }}</span>
            </flux:callout.heading>
            @if ($transaction->description)
                <flux:callout.text>{{ $transaction->description }}</flux:callout.text>
            @endif
        </flux:callout>
    @endif

    <div class="flex gap-2">
        <flux:spacer />

        <flux:modal.close>
            <flux:button variant="ghost">{{ __('actions.cancel') }}</flux:button>
        </flux:modal.close>

        <flux:button wire:click="delete" variant="danger" icon="trash" icon:variant="outline">
            {{ __('actions.delete') }}
        </flux:button>
    </div>
</flux:modal>
