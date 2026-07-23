<?php

use App\Models\Finance\Currency;
use App\Models\Finance\Wallet;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    #[Url]
    public string $currencyId = '';

    public string $currencySearch = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function wallets(): LengthAwarePaginator
    {
        return Wallet::query()
            ->with([
                'user' => fn ($query) => $query->withTrashed(),
                'currency' => fn ($query) => $query->withTrashed(),
            ])
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($query) {
                    $query->withTrashed()
                        ->where(function ($query) {
                            $query->where('first_name', 'like', "%{$this->search}%")
                                ->orWhere('last_name', 'like', "%{$this->search}%")
                                ->orWhere('email', 'like', "%{$this->search}%")
                                ->orWhere('mobile', 'like', "%{$this->search}%")
                                ->orWhere('username', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->currencyId, fn ($query) => $query->where('currency_id', $this->currencyId))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(config('general.per_page', 10));
    }

    #[Computed]
    public function currencies(): Collection
    {
        $currencies = Currency::query()
            ->withTrashed()
            ->when($this->currencySearch, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', "%{$this->currencySearch}%")
                        ->orWhere('symbol', 'like', "%{$this->currencySearch}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        if (blank($this->currencySearch) && filled($this->currencyId)) {
            $selected = Currency::query()
                ->withTrashed()
                ->whereKey($this->currencyId)
                ->whereNotIn('id', $currencies->pluck('id'))
                ->get();

            $currencies = $selected->merge($currencies);
        }

        return $currencies;
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

    public function updatedCurrencyId(): void
    {
        $this->resetPage();
    }

    public function refreshBalance(int $walletId): void
    {
        $wallet = Wallet::query()->findOrFail($walletId);
        $wallet->refresh();

        unset($this->wallets);

        Flux::toast(__('general.wallet_balance_refreshed'));
    }

    #[On('panels.administrator.finance-management.wallet.index.refresh')]
    public function refresh(): void
    {
        unset($this->wallets);
        unset($this->currencies);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.wallets') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" />
                <flux:breadcrumbs.item>{{ __('general.finance_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.wallets') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

        <flux:card>
            <div class="mb-4 grid gap-3 md:grid-cols-2">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable />

                <flux:select wire:model.live="currencyId" variant="combobox" :filter="false" clearable placeholder="{{ __('general.currency') }}...">
                    <x-slot name="input">
                        <flux:select.input wire:model.live.debounce.300ms="currencySearch" placeholder="{{ __('general.currency') }}..." />
                    </x-slot>

                    @foreach ($this->currencies as $currency)
                        <flux:select.option value="{{ $currency->id }}" wire:key="currency-option-{{ $currency->id }}">
                            <span dir="ltr">{{ $currency->symbol }}</span> — {{ $currency->trashed() ? __('general.deleted') : $currency->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:table :paginate="$this->wallets">
                <flux:table.columns>
                    <flux:table.column>{{ __('general.user') }}</flux:table.column>
                    <flux:table.column>{{ __('general.currency') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'balance'" :direction="$sortDirection" wire:click="sort('balance')">{{ __('general.balance') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'locked_balance'" :direction="$sortDirection" wire:click="sort('locked_balance')">{{ __('general.locked_balance') }}</flux:table.column>
                    <flux:table.column>{{ __('general.status') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->wallets as $wallet)
                        @php
                            $user = $wallet->user;
                            $currency = $wallet->currency;
                            $decimals = $currency?->decimals ?? 8;
                            $userLabel = ($user && ! $user->trashed()) ? $user->full_name : __('general.deleted');
                            $currencyLabel = ($currency && ! $currency->trashed()) ? $currency->name : __('general.deleted');
                            $currencySymbol = $currency?->symbol ?? __('general.deleted');
                        @endphp
                        <flux:table.row :key="$wallet->id">
                            <flux:table.cell>
                                <div class="space-y-0.5">
                                    <div class="font-medium">{{ $userLabel }}</div>
                                    @if ($user && ! $user->trashed())
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400" dir="ltr">
                                            {{ $user->username }} · {{ $user->email }} · {{ $user->mobile }}
                                        </div>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    @if ($currency?->logo && ! $currency->trashed())
                                        <img src="{{ asset('storage/' . $currency->logo) }}" alt="{{ $currencyLabel }}" class="size-6 rounded object-contain" />
                                    @else
                                        <flux:icon.circle-dollar-sign variant="outline" class="size-4 text-teal-500" />
                                    @endif
                                    <div>
                                        <div class="font-medium" dir="ltr">{{ $currencySymbol }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $currencyLabel }}</div>
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span dir="ltr">{{ number_format((float) $wallet->balance, $decimals) }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span dir="ltr">{{ number_format((float) $wallet->locked_balance, $decimals) }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $wallet->is_active ? 'green' : 'red' }}">
                                    {{ $wallet->is_active ? __('general.active') : __('general.inactive') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $wallet->created_at->toDynamicFormat('Y/m/d H:i:s') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.refresh_balance') }}">
                                        <flux:button size="xs" variant="primary" color="amber" icon="refresh-cw" icon:variant="outline" wire:click="refreshBalance({{ $wallet->id }})" />
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</div>
