<?php

use App\Enums\Sms\SmsDirectionEnum;
use App\Enums\Sms\SmsMessageStatusEnum;
use App\Models\Sms\Gateway;
use App\Models\Sms\Message;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $direction = '';

    public string $status = '';

    public string $gatewayId = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function messages(): LengthAwarePaginator
    {
        return Message::query()
            ->with(['gateway.provider', 'user'])
            ->withCount('recipients')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('number', 'like', "%{$this->search}%")
                        ->orWhere('body', 'like', "%{$this->search}%")
                        ->orWhere('reference_id', 'like', "%{$this->search}%");
                });
            })
            ->when($this->direction, fn ($query) => $query->where('direction', $this->direction))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->gatewayId, fn ($query) => $query->where('gateway_id', $this->gatewayId))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(config('general.per_page', 10));
    }

    #[Computed]
    public function gateways(): Collection
    {
        return Gateway::query()->orderBy('title')->get(['id', 'title', 'number']);
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

    public function updatedDirection(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedGatewayId(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'direction',
            'status',
            'gatewayId',
        ]);

        $this->resetPage();
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.sms_messages') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.sms_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.sms_messages') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

        <flux:card class="space-y-4">
            @php
                $activeFilters = collect([
                    $direction,
                    $status,
                    $gatewayId,
                ])->filter(fn ($v) => filled($v))->count();
            @endphp

            <div class="flex items-center gap-3">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="search"
                    placeholder="{{ __('general.search') }}..."
                    clearable
                    class="min-w-0 flex-1 max-w-xs"
                />

                <flux:dropdown align="end" class="shrink-0 ms-auto">
                    <flux:button
                        icon="funnel"
                        icon:variant="micro"
                        icon:class="text-zinc-400"
                    >
                        {{ __('general.filters') }}

                        <x-slot name="iconTrailing">
                            <flux:badge size="sm" class="-mr-1">
                                <span class="tabular-nums">{{ $activeFilters }}</span>
                            </flux:badge>
                        </x-slot>
                    </flux:button>

                    <flux:popover class="w-[min(100vw-2rem,20rem)] max-h-[70vh] overflow-y-auto flex flex-col gap-4">
                        <flux:select wire:model.live="direction" variant="listbox" searchable placeholder="{{ __('general.direction') }}..." clearable>
                            @foreach (SmsDirectionEnum::options() as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="status" variant="listbox" searchable placeholder="{{ __('general.status') }}..." clearable>
                            @foreach (SmsMessageStatusEnum::options() as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="gatewayId" variant="listbox" searchable placeholder="{{ __('general.sms_gateway') }}..." clearable>
                            @foreach ($this->gateways as $gateway)
                                <flux:select.option value="{{ $gateway->id }}">{{ $gateway->title }} ({{ $gateway->number }})</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:separator variant="subtle" />

                        <flux:button
                            variant="subtle"
                            size="sm"
                            class="justify-start -m-2 !px-2"
                            wire:click="clearFilters"
                        >
                            {{ __('general.clear_filters') }}
                        </flux:button>
                    </flux:popover>
                </flux:dropdown>
            </div>

            <flux:table :paginate="$this->messages">
                <flux:table.columns>
                    <flux:table.column>{{ __('general.direction') }}</flux:table.column>
                    <flux:table.column>{{ __('general.number') }}</flux:table.column>
                    <flux:table.column>{{ __('general.sms_gateway') }}</flux:table.column>
                    <flux:table.column>{{ __('general.parts_count') }}</flux:table.column>
                    <flux:table.column>{{ __('general.status') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->messages as $message)
                        <flux:table.row :key="$message->id">
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $message->direction->color() }}">{{ $message->direction->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell><span dir="ltr">{{ $message->number }}</span></flux:table.cell>
                            <flux:table.cell>{{ $message->gateway?->title }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ $message->parts_count }}</flux:badge>
                                <flux:badge size="sm" color="sky" class="ms-1">{{ $message->encoding->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $message->status->color() }}">{{ $message->status->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $message->created_at->toDynamicFormat('Y/m/d H:i:s') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:tooltip content="{{ __('general.message_details') }}">
                                    <flux:button size="xs" variant="primary" color="cyan" icon="eye" icon:variant="outline" href="{{ route('panels.administrator.sms-management.message.detail', $message) }}" wire:navigate />
                                </flux:tooltip>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</div>
