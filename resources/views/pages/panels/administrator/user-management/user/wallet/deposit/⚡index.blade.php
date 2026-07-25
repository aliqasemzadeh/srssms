<?php

use App\Enums\DepositStatusEnum;
use App\Models\Finance\Deposit;
use App\Models\Finance\Wallet;
use App\Models\User;
use Flux\DateRange;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public User $user;

    public Wallet $wallet;

    public string $search = '';

    public string $status = '';

    public string $method = '';

    public string $amountOperator = '';

    public string $amountValue = '';

    public string $amountMin = '';

    public string $amountMax = '';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public ?DateRange $dateRange = null;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public function mount(User $user, Wallet $wallet): void
    {
        abort_unless($wallet->user_id === $user->id, 404);

        $this->user = $user;
        $this->wallet = $wallet->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);
    }

    #[Computed]
    public function deposits(): LengthAwarePaginator
    {
        $allowedSorts = ['amount', 'fee', 'tax', 'amount_settled', 'created_at', 'status'];

        $sortBy = in_array($this->sortBy, $allowedSorts, true) ? $this->sortBy : 'created_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return Deposit::query()
            ->where('wallet_id', $this->wallet->id)
            ->with([
                'creator',
            ])
            ->withCount('transactions')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('tracking_code', 'like', "%{$this->search}%")
                        ->orWhere('admin_note', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->method, fn ($query) => $query->where('method', $this->method))
            ->when($this->amountOperator === 'gt' && filled($this->amountValue), function ($query) {
                $query->where('amount', '>', $this->amountValue);
            })
            ->when($this->amountOperator === 'lt' && filled($this->amountValue), function ($query) {
                $query->where('amount', '<', $this->amountValue);
            })
            ->when($this->amountOperator === 'between' && filled($this->amountMin) && filled($this->amountMax), function ($query) {
                $query->whereBetween('amount', [$this->amountMin, $this->amountMax]);
            })
            ->when(app()->getLocale() === 'fa', function ($query) {
                $query
                    ->when($this->dateFrom, fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom))
                    ->when($this->dateTo, fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo));
            }, function ($query) {
                $query->when(
                    $this->dateRange && $this->dateRange->start() && $this->dateRange->end(),
                    fn ($query) => $query->whereBetween('created_at', [
                        $this->dateRange->start()->copy()->startOfDay(),
                        $this->dateRange->end()->copy()->endOfDay(),
                    ])
                );
            })
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

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedMethod(): void
    {
        $this->resetPage();
    }

    public function updatedAmountOperator(): void
    {
        $this->amountValue = '';
        $this->amountMin = '';
        $this->amountMax = '';
        $this->resetPage();
    }

    public function updatedAmountValue(): void
    {
        $this->resetPage();
    }

    public function updatedAmountMin(): void
    {
        $this->resetPage();
    }

    public function updatedAmountMax(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedDateRange(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'status',
            'method',
            'amountOperator',
            'amountValue',
            'amountMin',
            'amountMax',
            'dateFrom',
            'dateTo',
            'dateRange',
        ]);

        $this->resetPage();
    }

    #[On('panels.administrator.user-management.user.wallet.deposit.index.refresh')]
    public function refresh(): void
    {
        $this->wallet->refresh()->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);

        unset($this->deposits);
    }
};
?>

<div>
    @php
        $currency = $wallet->currency;
        $currencyLabel = ($currency && ! $currency->trashed()) ? $currency->name : __('general.deleted');
        $currencySymbol = $currency?->symbol ?? __('general.deleted');
        $decimals = $currency?->decimals ?? 8;
        $isFa = app()->getLocale() === 'fa';
        $depositMethods = \App\Support\PaymentGateways::depositMethodOptions();
    @endphp

    <x-slot name="title">{{ __('general.deposits') }} - {{ $currencySymbol }} - {{ $user->full_name }} - {{ config('app.name') }}</x-slot>

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
                <flux:breadcrumbs.item>{{ __('general.deposits') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:button class="shrink-0" variant="primary" color="teal" icon="plus" wire:click="$dispatch('panels.administrator.user-management.user.wallet.deposit.create.assign-data')">
                {{ __('actions.create') }} {{ __('general.deposit') }}
            </flux:button>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
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

            <flux:card class="space-y-1">
                <flux:text class="text-sm text-zinc-500">{{ __('general.available_balance') }}</flux:text>
                <flux:heading size="md" dir="ltr" class="text-teal-600 dark:text-teal-400">{{ number_format((float) $wallet->available_balance, $decimals) }}</flux:heading>
            </flux:card>
        </div>

        <flux:card class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <flux:heading size="sm">{{ __('general.filters') }}</flux:heading>
                <flux:button size="sm" variant="ghost" icon="funnel" icon:variant="outline" wire:click="clearFilters">
                    {{ __('general.clear_filters') }}
                </flux:button>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable />

                <flux:select wire:model.live="status" variant="listbox" searchable placeholder="{{ __('general.status') }}..." clearable>
                    @foreach (DepositStatusEnum::cases() as $statusOption)
                        <flux:select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="method" variant="listbox" searchable placeholder="{{ __('general.method') }}..." clearable>
                    @foreach ($depositMethods as $methodKey => $methodLabel)
                        <flux:select.option value="{{ $methodKey }}">{{ __($methodLabel) }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="amountOperator" variant="listbox" searchable placeholder="{{ __('general.amount_filter') }}..." clearable>
                    <flux:select.option value="gt">{{ __('general.amount_greater_than') }}</flux:select.option>
                    <flux:select.option value="lt">{{ __('general.amount_less_than') }}</flux:select.option>
                    <flux:select.option value="between">{{ __('general.amount_between') }}</flux:select.option>
                </flux:select>
            </div>

            @if ($amountOperator === 'gt' || $amountOperator === 'lt')
                <div class="max-w-sm">
                    <flux:input
                        wire:model.live.debounce.300ms="amountValue"
                        type="number"
                        min="0"
                        step="0.00000001"
                        label="{{ __('general.amount') }}"
                        placeholder="100"
                        dir="ltr"
                        clearable
                    />
                </div>
            @elseif ($amountOperator === 'between')
                <div class="grid max-w-xl gap-3 md:grid-cols-2">
                    <flux:input
                        wire:model.live.debounce.300ms="amountMin"
                        type="number"
                        min="0"
                        step="0.00000001"
                        label="{{ __('general.amount_from') }}"
                        placeholder="100"
                        dir="ltr"
                        clearable
                    />
                    <flux:input
                        wire:model.live.debounce.300ms="amountMax"
                        type="number"
                        min="0"
                        step="0.00000001"
                        label="{{ __('general.amount_to') }}"
                        placeholder="900"
                        dir="ltr"
                        clearable
                    />
                </div>
            @endif

            <div class="grid gap-3 md:grid-cols-2">
                @if ($isFa)
                    <x-persian-date-picker
                        wire:model.live="dateFrom"
                        label="{{ __('general.date_from') }}"
                        placeholder="{{ __('general.date_from') }}"
                    />
                    <x-persian-date-picker
                        wire:model.live="dateTo"
                        label="{{ __('general.date_to') }}"
                        placeholder="{{ __('general.date_to') }}"
                    />
                @else
                    <div class="max-w-xl md:col-span-2">
                        <flux:date-picker
                            mode="range"
                            type="input"
                            wire:model.live="dateRange"
                            with-presets
                            clearable
                            label="{{ __('general.date_range') }}"
                            placeholder="{{ __('general.date_range') }}"
                        />
                    </div>
                @endif
            </div>

            <flux:table :paginate="$this->deposits">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'amount'" :direction="$sortDirection" wire:click="sort('amount')">{{ __('general.amount') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'fee'" :direction="$sortDirection" wire:click="sort('fee')">{{ __('general.fee') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'amount_settled'" :direction="$sortDirection" wire:click="sort('amount_settled')">{{ __('general.amount_settled') }}</flux:table.column>
                    <flux:table.column>{{ __('general.method') }}</flux:table.column>
                    <flux:table.column>{{ __('general.tracking_code') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection" wire:click="sort('status')">{{ __('general.status') }}</flux:table.column>
                    <flux:table.column>{{ __('general.transactions') }}</flux:table.column>
                    <flux:table.column>{{ __('general.creator') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->deposits as $deposit)
                        @php
                            $methodLabel = \App\Support\PaymentGateways::methodLabel((string) $deposit->method);
                        @endphp
                        <flux:table.row :key="$deposit->id">
                            <flux:table.cell variant="strong">
                                <span dir="ltr" class="text-green-600 dark:text-green-400">
                                    {{ number_format((float) $deposit->amount, $decimals) }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span dir="ltr">{{ number_format((float) $deposit->fee, $decimals) }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span dir="ltr">{{ number_format((float) $deposit->amount_settled, $decimals) }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $methodLabel }}</flux:table.cell>
                            <flux:table.cell>
                                <span dir="ltr">{{ $deposit->tracking_code ?: '—' }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $deposit->status->color() }}">
                                    {{ $deposit->status->label() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $deposit->transactions_count > 0 ? 'teal' : 'zinc' }}">
                                    {{ $deposit->transactions_count }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $deposit->creator?->full_name ?? '—' }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $deposit->created_at->toDynamicFormat('Y/m/d H:i:s') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.user.wallet.deposit.edit.assign-data', { deposit: {{ $deposit->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.user.wallet.deposit.delete.assign-data', { deposit: {{ $deposit->id }} })" />
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="10">
                                <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                                    <flux:icon.arrow-down-to-line variant="outline" class="size-8 text-zinc-400" />
                                    <flux:text>{{ __('general.no_results_found') }}</flux:text>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:user-management.user.wallet.deposit.create :user="$user" :wallet="$wallet" :key="'user-wallet-deposit-create-'.$wallet->id" />
    <livewire:user-management.user.wallet.deposit.edit :user="$user" :wallet="$wallet" :key="'user-wallet-deposit-edit-'.$wallet->id" />
    <livewire:user-management.user.wallet.deposit.delete :user="$user" :wallet="$wallet" :key="'user-wallet-deposit-delete-'.$wallet->id" />
</div>
