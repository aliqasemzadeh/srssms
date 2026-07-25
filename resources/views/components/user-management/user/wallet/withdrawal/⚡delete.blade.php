<?php

use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Models\Finance\Wallet;
use App\Models\Finance\Withdrawal;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use AuthorizesAdministratorPermissions;

    public User $user;

    public Wallet $wallet;

    public ?Withdrawal $withdrawal = null;

    public function mount(User $user, Wallet $wallet): void
    {
        abort_unless($wallet->user_id === $user->id, 404);

        $this->user = $user;
        $this->wallet = $wallet->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);
    }

    #[On('panels.administrator.user-management.user.wallet.withdrawal.delete.assign-data')]
    public function assignData(int $withdrawal): void
    {
        $this->authorizePermission('finance-management.withdrawal.delete');

        $this->withdrawal = Withdrawal::query()
            ->where('wallet_id', $this->wallet->id)
            ->with([
                'userAccount' => fn ($query) => $query->withTrashed(),
            ])
            ->findOrFail($withdrawal);

        Flux::modal('user-management.user.wallet.withdrawal.delete')->show();
    }

    public function delete(): void
    {
        $this->authorizePermission('finance-management.withdrawal.delete');

        if (! $this->withdrawal) {
            return;
        }

        $this->withdrawal->delete();

        $this->withdrawal = null;

        $this->dispatch('panels.administrator.user-management.user.wallet.withdrawal.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.withdrawal_deleted'));
    }
};
?>

@php
    $decimals = $wallet->currency?->decimals ?? 8;
    $currencySymbol = $wallet->currency?->symbol ?? '';
@endphp

<flux:modal name="user-management.user.wallet.withdrawal.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>

        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($withdrawal)
        <flux:callout icon="arrow-up-from-line" variant="secondary" inline>
            <flux:callout.heading>
                <span dir="ltr">{{ number_format((float) $withdrawal->amount, $decimals) }} {{ $currencySymbol }}</span>
            </flux:callout.heading>
            <flux:callout.text>
                {{ $withdrawal->status->label() }}
                @if ($withdrawal->tracking_code)
                    — <span dir="ltr">{{ $withdrawal->tracking_code }}</span>
                @endif
            </flux:callout.text>
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
