<?php

use App\Models\Finance\Currency;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public function mount(): void
    {
        if (session()->has('payment_status')) {
            $message = session('payment_message') ?: (
                session('payment_status') === 'success'
                    ? __('general.payment_success')
                    : __('general.payment_failed')
            );

            Flux::toast($message);
        }
    }

    #[Computed]
    public function currencies(): LengthAwarePaginator
    {
        $userId = Auth::id();

        return Currency::query()
            ->where('is_active', true)
            ->with([
                'wallets' => fn ($query) => $query->where('user_id', $userId),
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
};
?>

<div>
    <x-slot name="title">{{ __('general.wallets') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.wallets') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:button
                class="shrink-0"
                variant="primary"
                color="teal"
                icon="plus"
                href="{{ route('panels.user.wallet.charge') }}"
                wire:navigate
            >
                {{ __('general.charge_wallet') }}
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
                    <flux:table.column>{{ __('general.status') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->currencies as $currency)
                        @php
                            $wallet = $currency->wallets->first();
                            $decimals = $currency->decimals ?? 8;
                            $hasWallet = (bool) $wallet;
                            $balance = $hasWallet ? (float) $wallet->balance : 0;
                        @endphp
                        <flux:table.row :key="'currency-'.$currency->id">
                            <flux:table.cell variant="strong">
                                <div class="flex items-center gap-2">
                                    @if ($currency->logo)
                                        <img src="{{ asset('storage/' . $currency->logo) }}" alt="{{ $currency->name }}" class="size-6 rounded object-contain" />
                                    @else
                                        <flux:icon.wallet variant="outline" class="size-4 text-teal-500" />
                                    @endif
                                    <span dir="ltr">{{ $currency->symbol }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $currency->name }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <span dir="ltr">{{ number_format($balance, $decimals) }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($hasWallet)
                                    <flux:badge size="sm" color="{{ $wallet->is_active ? 'green' : 'red' }}">
                                        {{ $wallet->is_active ? __('general.active') : __('general.inactive') }}
                                    </flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc">{{ __('general.no_wallet_yet') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.charge_wallet') }}">
                                        <flux:button
                                            size="xs"
                                            variant="primary"
                                            color="teal"
                                            icon="plus"
                                            icon:variant="outline"
                                            href="{{ route('panels.user.wallet.charge', ['currency' => $currency->id]) }}"
                                            wire:navigate
                                        />
                                    </flux:tooltip>
                                    @if ($hasWallet)
                                        <flux:tooltip content="{{ __('general.transactions') }}">
                                            <flux:button
                                                size="xs"
                                                variant="primary"
                                                color="sky"
                                                icon="arrow-left-right"
                                                icon:variant="outline"
                                                href="{{ route('panels.user.wallet.transaction.index', $wallet) }}"
                                                wire:navigate
                                            />
                                        </flux:tooltip>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5">
                                <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                                    <flux:icon.wallet variant="outline" class="size-8 text-zinc-400" />
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
