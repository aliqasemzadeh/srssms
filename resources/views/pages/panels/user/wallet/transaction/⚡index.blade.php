<?php

use App\Models\Finance\Transaction;
use App\Models\Finance\Wallet;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public Wallet $wallet;

    public string $search = '';

    public string $type = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public function mount(Wallet $wallet): void
    {
        abort_unless($wallet->user_id === Auth::id(), 403);

        $this->wallet = $wallet->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);
    }

    #[Computed]
    public function transactions(): LengthAwarePaginator
    {
        $allowedSorts = ['amount', 'balance_after', 'created_at', 'type'];
        $sortBy = in_array($this->sortBy, $allowedSorts, true) ? $this->sortBy : 'created_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return Transaction::query()
            ->where('wallet_id', $this->wallet->id)
            ->when($this->search, function ($query) {
                $query->where('description', 'like', "%{$this->search}%");
            })
            ->when($this->type, fn ($query) => $query->where('type', $this->type))
            ->orderBy($sortBy, $sortDirection)
            ->paginate(config('general.per_page', 10));
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = $column === 'created_at' ? 'desc' : 'asc';
        }

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
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

    <x-slot name="title">{{ __('general.transactions') }} - {{ $currencySymbol }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item href="{{ route('panels.user.wallet.index') }}" wire:navigate>{{ __('general.wallets') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>
                    <span dir="ltr">{{ $currencySymbol }}</span>
                </flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.transactions') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:button
                class="shrink-0"
                variant="primary"
                color="teal"
                icon="plus"
                href="{{ route('panels.user.wallet.charge', ['currency' => $wallet->currency_id]) }}"
                wire:navigate
            >
                {{ __('general.charge_wallet') }}
            </flux:button>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
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
                <flux:heading size="md" dir="ltr" class="text-teal-600 dark:text-teal-400">
                    {{ number_format((float) $wallet->balance, $decimals) }}
                </flux:heading>
            </flux:card>
        </div>

        <flux:card class="space-y-4">
            <div class="grid gap-3 md:grid-cols-2">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable />

                <flux:select wire:model.live="type" variant="listbox" searchable placeholder="{{ __('general.type') }}..." clearable>
                    <flux:select.option value="credit">{{ __('general.transaction_type_credit') }}</flux:select.option>
                    <flux:select.option value="debit">{{ __('general.transaction_type_debit') }}</flux:select.option>
                </flux:select>
            </div>

            <flux:table :paginate="$this->transactions">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'type'" :direction="$sortDirection" wire:click="sort('type')">{{ __('general.type') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'amount'" :direction="$sortDirection" wire:click="sort('amount')">{{ __('general.amount') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'balance_after'" :direction="$sortDirection" wire:click="sort('balance_after')">{{ __('general.balance_after') }}</flux:table.column>
                    <flux:table.column>{{ __('general.description') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->transactions as $transaction)
                        <flux:table.row :key="$transaction->id">
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $transaction->type === 'credit' ? 'green' : 'red' }}">
                                    {{ __('general.transaction_type_'.$transaction->type) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell variant="strong">
                                <span dir="ltr" class="{{ $transaction->type === 'credit' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $transaction->type === 'credit' ? '+' : '-' }}{{ number_format((float) $transaction->amount, $decimals) }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span dir="ltr">
                                    {{ $transaction->balance_after !== null ? number_format((float) $transaction->balance_after, $decimals) : '—' }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $transaction->description ?: '—' }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $transaction->created_at->toDynamicFormat('Y/m/d H:i:s') }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5">
                                <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                                    <flux:icon.arrow-left-right variant="outline" class="size-8 text-zinc-400" />
                                    <flux:text>{{ __('general.no_results_found') }}</flux:text>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</div>
