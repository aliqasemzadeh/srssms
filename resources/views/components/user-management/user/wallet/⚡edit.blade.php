<?php

use App\Models\Finance\Wallet;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?Wallet $wallet = null;

    #[On('panels.administrator.user-management.user.wallet.edit.assign-data')]
    public function assignData(int $wallet): void
    {
        $this->wallet = Wallet::query()
            ->with([
                'user' => fn ($query) => $query->withTrashed(),
                'currency' => fn ($query) => $query->withTrashed(),
            ])
            ->findOrFail($wallet);

        Flux::modal('user-management.user.wallet.edit')->show();
    }
};
?>

<flux:modal name="user-management.user.wallet.edit" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.wallet') }}</flux:heading>
        <flux:subheading>{{ __('general.wallet_readonly_hint') }}</flux:subheading>
    </div>

    @if ($wallet)
        @php
            $currency = $wallet->currency;
            $decimals = $currency?->decimals ?? 8;
            $user = $wallet->user;
        @endphp

        <div class="space-y-6">
            <flux:input :value="$user?->full_name" label="{{ __('general.user') }}" readonly />

            <flux:input :value="($currency?->symbol ?? __('general.deleted')).' — '.($currency && ! $currency->trashed() ? $currency->name : __('general.deleted'))" label="{{ __('general.currency') }}" readonly dir="ltr" />

            <flux:input :value="number_format((float) $wallet->balance, $decimals)" label="{{ __('general.balance') }}" readonly dir="ltr" />

            <flux:input :value="number_format((float) $wallet->locked_balance, $decimals)" label="{{ __('general.locked_balance') }}" readonly dir="ltr" />

            <flux:input :value="$wallet->is_active ? __('general.active') : __('general.inactive')" label="{{ __('general.status') }}" readonly />

            <flux:input :value="$wallet->created_at->toDynamicFormat('Y/m/d H:i:s')" label="{{ __('general.created_at') }}" readonly />
        </div>
    @endif
</flux:modal>
