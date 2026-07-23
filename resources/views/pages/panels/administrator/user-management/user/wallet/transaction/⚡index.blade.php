<?php

use App\Models\Finance\Wallet;
use App\Models\User;
use Livewire\Component;

new class extends Component
{
    public User $user;

    public Wallet $wallet;

    public function mount(User $user, Wallet $wallet): void
    {
        abort_unless($wallet->user_id === $user->id, 404);

        $this->user = $user;
        $this->wallet = $wallet->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);
    }
};
?>

<div>
    @php
        $currency = $wallet->currency;
        $currencyLabel = ($currency && ! $currency->trashed()) ? $currency->name : __('general.deleted');
        $currencySymbol = $currency?->symbol ?? __('general.deleted');
        $decimals = $currency?->decimals ?? 8;
    @endphp

    <x-slot name="title">{{ __('general.transactions') }} - {{ $currencySymbol }} - {{ $user->full_name }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.user_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.user-management.user.index') }}" wire:navigate>{{ __('general.users') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.user-management.user.wallet.index', $user) }}" wire:navigate>{{ $user->full_name }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.user-management.user.wallet.index', $user) }}" wire:navigate>{{ __('general.wallets') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>
                    <span dir="ltr">{{ $currencySymbol }}</span>
                </flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.transactions') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <flux:card class="space-y-1">
                <flux:text class="text-sm text-zinc-500">{{ __('general.currency') }}</flux:text>
                <div class="flex items-center gap-2">
                    @if ($currency?->logo && ! $currency->trashed())
                        <img src="{{ asset('storage/' . $currency->logo) }}" alt="{{ $currencyLabel }}" class="size-6 rounded object-contain" />
                    @else
                        <flux:icon.wallet variant="outline" class="size-4 text-teal-500" />
                    @endif
                    <flux:heading size="md">
                        <span dir="ltr">{{ $currencySymbol }}</span>
                        — {{ $currencyLabel }}
                    </flux:heading>
                </div>
            </flux:card>

            <flux:card class="space-y-1">
                <flux:text class="text-sm text-zinc-500">{{ __('general.balance') }}</flux:text>
                <flux:heading size="md" dir="ltr">{{ number_format((float) $wallet->balance, $decimals) }}</flux:heading>
            </flux:card>

            <flux:card class="space-y-1">
                <flux:text class="text-sm text-zinc-500">{{ __('general.locked_balance') }}</flux:text>
                <flux:heading size="md" dir="ltr">{{ number_format((float) $wallet->locked_balance, $decimals) }}</flux:heading>
            </flux:card>
        </div>

        <flux:card>
            <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
                <flux:icon.arrow-left-right variant="outline" class="size-10 text-zinc-400" />
                <flux:heading size="lg">{{ __('general.transactions') }}</flux:heading>
                <flux:text>{{ __('general.coming_soon') }}</flux:text>
            </div>
        </flux:card>
    </div>
</div>
