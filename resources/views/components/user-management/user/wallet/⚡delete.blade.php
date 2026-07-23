<?php

use App\Models\Finance\Wallet;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?Wallet $wallet = null;

    #[On('panels.administrator.user-management.user.wallet.delete.assign-data')]
    public function assignData(int $wallet): void
    {
        $this->wallet = Wallet::query()
            ->with([
                'currency' => fn ($query) => $query->withTrashed(),
            ])
            ->findOrFail($wallet);

        Flux::modal('user-management.user.wallet.delete')->show();
    }

    public function delete(): void
    {
        if (! $this->wallet) {
            return;
        }

        $this->wallet->delete();

        $this->wallet = null;

        $this->dispatch('panels.administrator.user-management.user.wallet.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.wallet_deleted'));
    }
};
?>

<flux:modal name="user-management.user.wallet.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>

        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($wallet)
        @php
            $currency = $wallet->currency;
            $currencyLabel = ($currency && ! $currency->trashed()) ? $currency->name : __('general.deleted');
            $currencySymbol = $currency?->symbol ?? __('general.deleted');
        @endphp

        <flux:callout icon="wallet" variant="secondary" inline>
            <flux:callout.heading>
                <span dir="ltr">{{ $currencySymbol }}</span> — {{ $currencyLabel }}
            </flux:callout.heading>
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
