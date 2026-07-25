<?php

use App\Models\Sms\Token;
use App\Models\Sms\TokenLog;
use Flux\DateRange;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $tokenId = '';

    public string $tokenSearch = '';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public ?DateRange $dateRange = null;

    public ?int $detailId = null;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function logs(): LengthAwarePaginator
    {
        return TokenLog::query()
            ->where('user_id', Auth::id())
            ->with('token')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('ip', 'like', "%{$this->search}%")
                        ->orWhere('path', 'like', "%{$this->search}%")
                        ->orWhere('method', 'like', "%{$this->search}%");
                });
            })
            ->when($this->tokenId, fn ($query) => $query->where('token_id', $this->tokenId))
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
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(config('general.per_page', 10));
    }

    #[Computed]
    public function tokens(): Collection
    {
        $tokens = Token::query()
            ->where('user_id', Auth::id())
            ->when($this->tokenSearch, fn ($query) => $query->where('name', 'like', "%{$this->tokenSearch}%"))
            ->orderBy('name')
            ->limit(20)
            ->get();

        if (blank($this->tokenSearch) && filled($this->tokenId)) {
            $selected = Token::query()
                ->where('user_id', Auth::id())
                ->whereKey($this->tokenId)
                ->whereNotIn('id', $tokens->pluck('id'))
                ->get();

            $tokens = $selected->merge($tokens);
        }

        return $tokens;
    }

    #[Computed]
    public function detailLog(): ?TokenLog
    {
        if (! $this->detailId) {
            return null;
        }

        return TokenLog::query()
            ->where('user_id', Auth::id())
            ->with('token')
            ->find($this->detailId);
    }

    public function showDetail(int $logId): void
    {
        $this->detailId = $logId;
        unset($this->detailLog);
        \Flux\Flux::modal('sms.token.log.detail')->show();
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

    public function updatedTokenId(): void
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
};
?>

@php($isFa = app()->getLocale() === 'fa')

<div>
    <x-slot name="title">{{ __('general.sms_token_logs') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item href="{{ route('panels.user.sms.token.index') }}" wire:navigate>{{ __('general.sms_tokens') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.sms_token_logs') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:button variant="primary" color="zinc" icon="arrow-right" :href="route('panels.user.sms.token.index')" wire:navigate>
                {{ __('general.sms_tokens') }}
            </flux:button>
        </div>

        <flux:card class="space-y-4">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" label="{{ __('general.search') }}" placeholder="{{ __('general.search') }}..." clearable />

                <flux:select wire:model.live="tokenId" variant="combobox" :filter="false" clearable placeholder="{{ __('general.sms_token') }}...">
                    <x-slot name="input">
                        <flux:select.input wire:model.live.debounce.300ms="tokenSearch" placeholder="{{ __('general.sms_token') }}..." />
                    </x-slot>
                    @foreach ($this->tokens as $token)
                        <flux:select.option value="{{ $token->id }}" wire:key="token-filter-{{ $token->id }}">{{ $token->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

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

            <flux:table :paginate="$this->logs">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column>{{ __('general.sms_token') }}</flux:table.column>
                    <flux:table.column>{{ __('general.ip') }}</flux:table.column>
                    <flux:table.column>{{ __('general.method') }}</flux:table.column>
                    <flux:table.column>{{ __('general.status_code') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->logs as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell>{{ $log->created_at->toDynamicFormat('Y/m/d H:i:s') }}</flux:table.cell>
                            <flux:table.cell>{{ $log->token?->name ?: '—' }}</flux:table.cell>
                            <flux:table.cell><span dir="ltr">{{ $log->ip ?: '—' }}</span></flux:table.cell>
                            <flux:table.cell><span dir="ltr">{{ $log->method }}</span></flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $log->status_code >= 400 ? 'red' : 'green' }}">{{ $log->status_code }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:tooltip content="{{ __('general.view') }}">
                                    <flux:button size="xs" variant="primary" color="zinc" icon="eye" icon:variant="outline" wire:click="showDetail({{ $log->id }})" />
                                </flux:tooltip>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <flux:modal name="sms.token.log.detail" flyout position="right" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.sms_token_log_detail') }}</flux:heading>
        </div>

        @if ($this->detailLog)
            <div class="space-y-4">
                <div>
                    <flux:text class="mb-2 text-sm opacity-70">{{ __('general.request') }}</flux:text>
                    <pre class="overflow-x-auto rounded-lg bg-zinc-100 p-4 text-xs dark:bg-zinc-800" dir="ltr">{{ json_encode($this->detailLog->request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
                <div>
                    <flux:text class="mb-2 text-sm opacity-70">{{ __('general.response') }}</flux:text>
                    <pre class="overflow-x-auto rounded-lg bg-zinc-100 p-4 text-xs dark:bg-zinc-800" dir="ltr">{{ json_encode($this->detailLog->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
