<?php

use App\Models\Sms\Gateway;
use App\Models\User;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public Gateway $gateway;

    public string $search = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public function mount(Gateway $gateway): void
    {
        $this->gateway = $gateway->loadMissing('provider');
    }

    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return $this->gateway->users()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('mobile', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('username', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortBy === 'full_name' ? 'first_name' : $this->sortBy, $this->sortDirection)
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

    #[On('panels.administrator.sms-management.gateway.user.index.refresh')]
    public function refresh(): void
    {
        unset($this->users);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.gateway_users') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.sms_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.sms-management.gateway.index') }}" wire:navigate>{{ __('general.sms_gateways') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $gateway->title }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.gateway_users') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <div class="sm:hidden shrink-0">
                <flux:dropdown align="end">
                    <flux:button icon:trailing="chevron-down">{{ __('general.actions') }}</flux:button>
                    <flux:menu>
                        <flux:menu.item icon="arrow-right" href="{{ route('panels.administrator.sms-management.gateway.index') }}" wire:navigate>
                            {{ __('general.sms_gateways') }}
                        </flux:menu.item>
                        @can('sms-management.gateway.user.create')
                        <flux:menu.item icon="user-plus" wire:click="$dispatch('panels.administrator.sms-management.gateway.user.access.assign-data', { gateway: {{ $gateway->id }} })">
                            {{ __('general.gateway_access') }}
                        </flux:menu.item>
                        @endcan
                    </flux:menu>
                </flux:dropdown>
            </div>

            <div class="hidden items-center gap-2 sm:flex shrink-0">
                <flux:button variant="primary" color="zinc" icon="arrow-right" href="{{ route('panels.administrator.sms-management.gateway.index') }}" wire:navigate>
                    {{ __('general.sms_gateways') }}
                </flux:button>
                @can('sms-management.gateway.user.create')
                <flux:button variant="primary" color="teal" icon="user-plus" wire:click="$dispatch('panels.administrator.sms-management.gateway.user.access.assign-data', { gateway: {{ $gateway->id }} })">
                    {{ __('general.gateway_access') }}
                </flux:button>
                @endcan
            </div>
        </div>

        <flux:callout icon="radio-tower" variant="secondary">
            <flux:callout.heading>
                {{ $gateway->title }} — <span dir="ltr">{{ $gateway->number }}</span>
            </flux:callout.heading>
            <flux:callout.text>{{ $gateway->provider?->name }}</flux:callout.text>
        </flux:callout>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable class="min-w-0 flex-1 max-w-xs" />
            </div>

            <flux:table :paginate="$this->users">
                <flux:table.columns>
                    <flux:table.column>{{ __('general.name') }}</flux:table.column>
                    <flux:table.column>{{ __('general.mobile') }}</flux:table.column>
                    <flux:table.column>{{ __('general.email') }}</flux:table.column>
                    <flux:table.column>{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->users as $user)
                        <flux:table.row :key="$user->id">
                            <flux:table.cell variant="strong">{{ $user->full_name }}</flux:table.cell>
                            <flux:table.cell><span dir="ltr">{{ $user->mobile }}</span></flux:table.cell>
                            <flux:table.cell>{{ $user->email }}</flux:table.cell>
                            <flux:table.cell>{{ $user->pivot->created_at?->toDynamicFormat('Y/m/d H:i:s') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                @can('sms-management.gateway.user.delete')
                                <flux:tooltip content="{{ __('general.revoke_access') }}">
                                    <flux:button size="xs" variant="danger" icon="user-minus" icon:variant="outline" wire:click="$dispatch('panels.administrator.sms-management.gateway.user.delete.assign-data', { gateway: {{ $gateway->id }}, user: {{ $user->id }} })" />
                                </flux:tooltip>
                                @endcan
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:sms-management.gateway.user.access :key="'gateway-user-access-'.$gateway->id" />
    <livewire:sms-management.gateway.user.delete :key="'gateway-user-delete-'.$gateway->id" />
</div>
