<?php

use App\Enums\Sms\SmsGatewayAccessTypeEnum;
use App\Enums\Sms\SmsGatewayUsageTypeEnum;
use App\Models\Sms\Gateway;
use App\Models\Sms\Provider;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $providerId = '';

    public string $accessType = '';

    public string $usageType = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function gateways(): LengthAwarePaginator
    {
        return Gateway::query()
            ->with('provider')
            ->withCount('users')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('title', 'like', "%{$this->search}%")
                        ->orWhere('number', 'like', "%{$this->search}%");
                });
            })
            ->when($this->providerId, fn ($query) => $query->where('provider_id', $this->providerId))
            ->when($this->accessType, fn ($query) => $query->where('access_type', $this->accessType))
            ->when($this->usageType, fn ($query) => $query->where('usage_type', $this->usageType))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(config('general.per_page', 10));
    }

    #[Computed]
    public function providers(): Collection
    {
        return Provider::query()->orderBy('name')->get(['id', 'name']);
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

    public function updatedProviderId(): void
    {
        $this->resetPage();
    }

    public function updatedAccessType(): void
    {
        $this->resetPage();
    }

    public function updatedUsageType(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'providerId',
            'accessType',
            'usageType',
        ]);

        $this->resetPage();
    }

    #[On('panels.administrator.sms-management.gateway.index.refresh')]
    public function refresh(): void
    {
        unset($this->gateways);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.sms_gateways') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.sms_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.sms_gateways') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            @can('sms-management.gateway.create')
            <div class="sm:hidden shrink-0">
                <flux:dropdown align="end">
                    <flux:button icon:trailing="chevron-down">{{ __('general.actions') }}</flux:button>
                    <flux:menu>
                        <flux:menu.item
                            icon="plus"
                            wire:click="$dispatch('panels.administrator.sms-management.gateway.create.assign-data')"
                        >
                            {{ __('actions.create') }} {{ __('general.sms_gateway') }}
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>

            <div class="hidden items-center gap-2 sm:flex shrink-0">
                <flux:button
                    variant="primary"
                    color="teal"
                    icon="plus"
                    wire:click="$dispatch('panels.administrator.sms-management.gateway.create.assign-data')"
                >
                    {{ __('actions.create') }} {{ __('general.sms_gateway') }}
                </flux:button>
            </div>
            @endcan
        </div>

        <flux:card class="space-y-4">
            @php
                $activeFilters = collect([
                    $providerId,
                    $accessType,
                    $usageType,
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
                        <flux:select wire:model.live="providerId" variant="listbox" searchable placeholder="{{ __('general.provider') }}..." clearable>
                            @foreach ($this->providers as $provider)
                                <flux:select.option value="{{ $provider->id }}">{{ $provider->name }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="accessType" variant="listbox" searchable placeholder="{{ __('general.gateway_access_type') }}..." clearable>
                            @foreach (SmsGatewayAccessTypeEnum::options() as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="usageType" variant="listbox" searchable placeholder="{{ __('general.gateway_usage_type') }}..." clearable>
                            @foreach (SmsGatewayUsageTypeEnum::options() as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
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

            <flux:table :paginate="$this->gateways">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'title'" :direction="$sortDirection" wire:click="sort('title')">{{ __('general.title') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'number'" :direction="$sortDirection" wire:click="sort('number')">{{ __('general.gateway_number') }}</flux:table.column>
                    <flux:table.column>{{ __('general.provider') }}</flux:table.column>
                    <flux:table.column>{{ __('general.gateway_access_type') }}</flux:table.column>
                    <flux:table.column>{{ __('general.gateway_usage_type') }}</flux:table.column>
                    <flux:table.column>{{ __('general.is_public') }}</flux:table.column>
                    <flux:table.column>{{ __('general.sms_rate') }}</flux:table.column>
                    <flux:table.column>{{ __('general.gateway_users') }}</flux:table.column>
                    <flux:table.column>{{ __('general.status') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->gateways as $gateway)
                        <flux:table.row :key="$gateway->id">
                            <flux:table.cell variant="strong">{{ $gateway->title }}</flux:table.cell>
                            <flux:table.cell><span dir="ltr">{{ $gateway->number }}</span></flux:table.cell>
                            <flux:table.cell>{{ $gateway->provider?->name }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $gateway->access_type->color() }}">{{ $gateway->access_type->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $gateway->usage_type->color() }}">{{ $gateway->usage_type->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $gateway->is_public ? 'green' : 'zinc' }}">
                                    {{ $gateway->is_public ? __('general.yes') : __('general.no') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span dir="ltr">{{ number_format($gateway->sms_rate) }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ $gateway->users_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $gateway->is_active ? 'green' : 'red' }}">
                                    {{ $gateway->is_active ? __('general.active') : __('general.inactive') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $gateway->created_at->toDynamicFormat('Y/m/d H:i:s') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    @can('sms-management.gateway.user.view')
                                    <flux:tooltip content="{{ __('general.gateway_users') }}">
                                        <flux:button size="xs" variant="primary" color="teal" icon="users" icon:variant="outline" href="{{ route('panels.administrator.sms-management.gateway.user.index', $gateway) }}" wire:navigate />
                                    </flux:tooltip>
                                    @endcan
                                    @can('sms-management.gateway.edit')
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.sms-management.gateway.edit.assign-data', { gateway: {{ $gateway->id }} })" />
                                    </flux:tooltip>
                                    @endcan
                                    @can('sms-management.gateway.delete')
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.administrator.sms-management.gateway.delete.assign-data', { gateway: {{ $gateway->id }} })" />
                                    </flux:tooltip>
                                    @endcan
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:sms-management.gateway.create :key="'gateway-create'" />
    <livewire:sms-management.gateway.edit :key="'gateway-edit'" />
    <livewire:sms-management.gateway.delete :key="'gateway-delete'" />
</div>
