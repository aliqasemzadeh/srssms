<?php

use App\Models\Sms\Message;
use Flux\DateRange;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $source = '';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public ?DateRange $dateRange = null;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function messages(): LengthAwarePaginator
    {
        return Message::query()
            ->where('user_id', Auth::id())
            ->with('gateway')
            ->withCount('recipients')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('body', 'like', "%{$this->search}%")
                        ->orWhere('number', 'like', "%{$this->search}%");
                });
            })
            ->when($this->source, fn ($query) => $query->where('source', $this->source))
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

    public function updatedSource(): void
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
    <x-slot name="title">{{ __('general.sms_messages') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.sms_messages') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:button variant="primary" color="teal" icon="send" :href="route('panels.user.sms.send')" wire:navigate>
                {{ __('general.send_sms') }}
            </flux:button>
        </div>

        <flux:card class="space-y-4">
            <div @class([
                'grid gap-3 md:grid-cols-2',
                'xl:grid-cols-4' => $isFa,
                'xl:grid-cols-3' => ! $isFa,
            ])>
                <flux:input wire:model.live.debounce.300ms="search" icon="search" label="{{ __('general.search') }}" placeholder="{{ __('general.search') }}..." clearable />

                <flux:select wire:model.live="source" variant="listbox" searchable clearable placeholder="{{ __('general.source') }}...">
                    @foreach (\App\Enums\Sms\SmsMessageSourceEnum::options() as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

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
                    <flux:date-picker
                        mode="range"
                        type="input"
                        wire:model.live="dateRange"
                        with-presets
                        clearable
                        label="{{ __('general.date_range') }}"
                        placeholder="{{ __('general.date_range') }}"
                    />
                @endif
            </div>

            <flux:table :paginate="$this->messages">
                <flux:table.columns>
                    <flux:table.column>{{ __('general.message_body') }}</flux:table.column>
                    <flux:table.column>{{ __('general.sms_gateway') }}</flux:table.column>
                    <flux:table.column>{{ __('general.source') }}</flux:table.column>
                    <flux:table.column>{{ __('general.recipients') }}</flux:table.column>
                    <flux:table.column>{{ __('general.parts_count') }}</flux:table.column>
                    <flux:table.column>{{ __('general.cost') }}</flux:table.column>
                    <flux:table.column>{{ __('general.status') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->messages as $message)
                        <flux:table.row :key="$message->id">
                            <flux:table.cell>{{ \Illuminate\Support\Str::limit($message->body, 50) }}</flux:table.cell>
                            <flux:table.cell>{{ $message->gateway?->title ?: '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $message->source->color() }}">{{ $message->source->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="sky">{{ $message->recipients_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $message->parts_count }}</flux:table.cell>
                            <flux:table.cell>
                                {{ $message->cost !== null ? number_format($message->cost).' '.__('general.rial') : '—' }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $message->status->color() }}">{{ $message->status->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $message->created_at->toDynamicFormat('Y/m/d H:i') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:tooltip content="{{ __('general.view') }}">
                                    <flux:button size="xs" variant="primary" color="zinc" icon="eye" icon:variant="outline" :href="route('panels.user.sms.message.detail', $message)" wire:navigate />
                                </flux:tooltip>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</div>
