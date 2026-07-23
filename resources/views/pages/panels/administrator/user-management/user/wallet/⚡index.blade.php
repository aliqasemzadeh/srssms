<?php

use App\Models\Finance\Currency;
use App\Models\Finance\Wallet;
use App\Models\User;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public User $user;

    public string $search = '';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    #[Computed]
    public function currencies(): LengthAwarePaginator
    {
        return Currency::query()
            ->where('is_active', true)
            ->with([
                'wallets' => fn ($query) => $query->where('user_id', $this->user->id),
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('symbol', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(config('general.per_page', 10));
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function rowClicked(int $currencyId): void
    {
        $hasWallet = Wallet::query()
            ->where('user_id', $this->user->id)
            ->where('currency_id', $currencyId)
            ->exists();

        if ($hasWallet) {
            return;
        }

        $this->addWallet($currencyId);
    }

    public function addWallet(int $currencyId): void
    {
        $currency = Currency::query()
            ->whereKey($currencyId)
            ->where('is_active', true)
            ->firstOrFail();

        $existing = Wallet::withTrashed()
            ->where('user_id', $this->user->id)
            ->where('currency_id', $currency->id)
            ->first();

        if ($existing && ! $existing->trashed()) {
            Flux::toast(__('general.wallet_already_exists'));

            return;
        }

        DB::transaction(function () use ($existing, $currency) {
            if ($existing?->trashed()) {
                $existing->restore();
                $existing->update([
                    'balance' => 0,
                    'locked_balance' => 0,
                    'is_active' => true,
                ]);

                return;
            }

            Wallet::query()->create([
                'user_id' => $this->user->id,
                'currency_id' => $currency->id,
                'balance' => 0,
                'locked_balance' => 0,
                'is_active' => true,
            ]);
        });

        unset($this->currencies);

        Flux::toast(__('general.wallet_created'));
    }

    public function refreshBalance(int $walletId): void
    {
        $wallet = Wallet::query()
            ->where('user_id', $this->user->id)
            ->findOrFail($walletId);

        $wallet->refresh();

        unset($this->currencies);

        Flux::toast(__('general.wallet_balance_refreshed'));
    }

    #[On('panels.administrator.user-management.user.wallet.index.refresh')]
    public function refresh(): void
    {
        unset($this->currencies);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.wallets') }} - {{ $user->full_name }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.user_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.user-management.user.index') }}" wire:navigate>{{ __('general.users') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $user->full_name }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.wallets') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:button class="shrink-0" variant="primary" color="teal" icon="plus" wire:click="$dispatch('panels.administrator.user-management.user.wallet.create.assign-data')">
                {{ __('actions.create') }} {{ __('general.wallet') }}
            </flux:button>
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable />
            </div>

            <flux:table :paginate="$this->currencies">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'symbol'" :direction="$sortDirection" wire:click="sort('symbol')">{{ __('general.symbol') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('general.currency') }}</flux:table.column>
                    <flux:table.column>{{ __('general.balance') }}</flux:table.column>
                    <flux:table.column>{{ __('general.locked_balance') }}</flux:table.column>
                    <flux:table.column>{{ __('general.status') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->currencies as $currency)
                        @php
                            $wallet = $currency->wallets->first();
                            $decimals = $currency->decimals ?? 8;
                            $hasWallet = (bool) $wallet;
                        @endphp
                        <flux:table.row
                            :key="'currency-'.$currency->id"
                            class="{{ $hasWallet ? '' : 'opacity-40 hover:opacity-70 cursor-pointer' }}"
                            wire:click="rowClicked({{ $currency->id }})"
                        >
                            <flux:table.cell variant="strong">
                                @if ($hasWallet)
                                    <a
                                        href="{{ route('panels.administrator.user-management.user.wallet.transaction.index', ['user' => $user, 'wallet' => $wallet]) }}"
                                        class="flex items-center gap-2 hover:text-teal-600 dark:hover:text-teal-400"
                                        wire:navigate
                                        wire:click.stop
                                    >
                                        @if ($currency->logo)
                                            <img src="{{ asset('storage/' . $currency->logo) }}" alt="{{ $currency->name }}" class="size-6 rounded object-contain" />
                                        @else
                                            <flux:icon.wallet variant="outline" class="size-4 text-teal-500" />
                                        @endif
                                        <span dir="ltr">{{ $currency->symbol }}</span>
                                    </a>
                                @else
                                    <div class="flex items-center gap-2">
                                        @if ($currency->logo)
                                            <img src="{{ asset('storage/' . $currency->logo) }}" alt="{{ $currency->name }}" class="size-6 rounded object-contain" />
                                        @else
                                            <flux:icon.wallet variant="outline" class="size-4 text-zinc-400" />
                                        @endif
                                        <span dir="ltr">{{ $currency->symbol }}</span>
                                    </div>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($hasWallet)
                                    <a
                                        href="{{ route('panels.administrator.user-management.user.wallet.transaction.index', ['user' => $user, 'wallet' => $wallet]) }}"
                                        class="hover:text-teal-600 dark:hover:text-teal-400"
                                        wire:navigate
                                        wire:click.stop
                                    >
                                        {{ $currency->name }}
                                    </a>
                                @else
                                    <span>{{ $currency->name }}</span>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('general.click_to_add_wallet') }}</div>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($hasWallet)
                                    <span dir="ltr">{{ number_format((float) $wallet->balance, $decimals) }}</span>
                                @else
                                    <span dir="ltr" class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($hasWallet)
                                    <span dir="ltr">{{ number_format((float) $wallet->locked_balance, $decimals) }}</span>
                                @else
                                    <span dir="ltr" class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($hasWallet)
                                    <flux:badge size="sm" color="{{ $wallet->is_active ? 'green' : 'red' }}">
                                        {{ $wallet->is_active ? __('general.active') : __('general.inactive') }}
                                    </flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc">{{ __('general.inactive') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                @if ($hasWallet)
                                    <div class="flex justify-end gap-2" wire:click.stop>
                                        <flux:tooltip content="{{ __('general.transactions') }}">
                                            <flux:button
                                                size="xs"
                                                variant="primary"
                                                color="teal"
                                                icon="arrow-left-right"
                                                icon:variant="outline"
                                                :href="route('panels.administrator.user-management.user.wallet.transaction.index', ['user' => $user, 'wallet' => $wallet])"
                                                wire:navigate
                                            />
                                        </flux:tooltip>
                                        <flux:tooltip content="{{ __('general.refresh_balance') }}">
                                            <flux:button size="xs" variant="primary" color="amber" icon="refresh-cw" icon:variant="outline" wire:click="refreshBalance({{ $wallet->id }})" />
                                        </flux:tooltip>
                                        <flux:tooltip content="{{ __('general.edit') }}">
                                            <flux:button size="xs" variant="primary" color="blue" icon="eye" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.user.wallet.edit.assign-data', { wallet: {{ $wallet->id }} })" />
                                        </flux:tooltip>
                                        <flux:tooltip content="{{ __('general.delete') }}">
                                            <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.user.wallet.delete.assign-data', { wallet: {{ $wallet->id }} })" />
                                        </flux:tooltip>
                                    </div>
                                @else
                                    <div class="flex justify-end" wire:click.stop>
                                        <flux:tooltip content="{{ __('general.click_to_add_wallet') }}">
                                            <flux:button size="xs" variant="primary" color="teal" icon="plus" icon:variant="outline" wire:click="addWallet({{ $currency->id }})" />
                                        </flux:tooltip>
                                    </div>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:user-management.user.wallet.create :user="$user" :key="'user-wallet-create-'.$user->id" />
    <livewire:user-management.user.wallet.edit :key="'user-wallet-edit-'.$user->id" />
    <livewire:user-management.user.wallet.delete :key="'user-wallet-delete-'.$user->id" />
</div>
