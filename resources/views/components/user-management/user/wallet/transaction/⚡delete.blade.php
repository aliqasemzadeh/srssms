<?php

use App\Models\Finance\Transaction;
use App\Models\Finance\Wallet;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public User $user;

    public Wallet $wallet;

    public ?Transaction $transaction = null;

    public function mount(User $user, Wallet $wallet): void
    {
        abort_unless($wallet->user_id === $user->id, 404);

        $this->user = $user;
        $this->wallet = $wallet->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);
    }

    #[On('panels.administrator.user-management.user.wallet.transaction.delete.assign-data')]
    public function assignData(int $transaction): void
    {
        $this->transaction = Transaction::query()
            ->where('wallet_id', $this->wallet->id)
            ->findOrFail($transaction);

        Flux::modal('user-management.user.wallet.transaction.delete')->show();
    }

    public function confirmDelete(): void
    {
        if (! $this->transaction) {
            return;
        }

        try {
            DB::transaction(function () {
                $wallet = Wallet::query()->lockForUpdate()->findOrFail($this->wallet->id);
                $transaction = Transaction::query()
                    ->where('wallet_id', $wallet->id)
                    ->lockForUpdate()
                    ->findOrFail($this->transaction->id);

                if ($transaction->type === Transaction::TYPE_CREDIT) {
                    $wallet->balance = bcsub((string) $wallet->balance, (string) $transaction->amount, 8);
                } else {
                    $wallet->balance = bcadd((string) $wallet->balance, (string) $transaction->amount, 8);
                }

                if (bccomp((string) $wallet->balance, (string) $wallet->locked_balance, 8) < 0) {
                    throw ValidationException::withMessages([
                        'transaction' => __('general.transaction_delete_insufficient_balance'),
                    ]);
                }

                $wallet->save();
                $transaction->delete();
            });
        } catch (ValidationException $exception) {
            Flux::toast(
                text: collect($exception->errors())->flatten()->first() ?: __('general.transaction_delete_insufficient_balance'),
                variant: 'danger',
            );

            return;
        }

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

        <flux:button type="button" wire:click="confirmDelete" variant="danger" icon="trash" icon:variant="outline">
            {{ __('actions.delete') }}
        </flux:button>
    </div>
</flux:modal>
