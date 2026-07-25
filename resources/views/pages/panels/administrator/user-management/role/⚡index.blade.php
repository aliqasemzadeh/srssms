<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function roles(): LengthAwarePaginator
    {
        return Role::query()
            ->withCount(['permissions', 'users'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%");
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

    #[On('panels.administrator.user-management.role.index.refresh')]
    public function refresh(): void
    {
        unset($this->roles);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.roles') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" />
                <flux:breadcrumbs.item>{{ __('general.user_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.roles') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            @can('user-management.role.create')
            <div class="sm:hidden shrink-0">
                <flux:dropdown align="end">
                    <flux:button icon:trailing="chevron-down">{{ __('general.actions') }}</flux:button>
                    <flux:menu>
                        <flux:menu.item icon="plus" wire:click="$dispatch('panels.administrator.user-management.role.create.assign-data')">
                            {{ __('actions.create') }} {{ __('general.role') }}
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>

            <div class="hidden items-center gap-2 sm:flex shrink-0">
                <flux:button variant="primary" color="teal" icon="plus" wire:click="$dispatch('panels.administrator.user-management.role.create.assign-data')">
                    {{ __('actions.create') }} {{ __('general.role') }}
                </flux:button>
            </div>
            @endcan
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable class="min-w-0 flex-1 max-w-xs" />
            </div>

            <flux:table :paginate="$this->roles">
                <flux:table.columns>
                    <flux:table.column>{{ __('general.name') }}</flux:table.column>
                    <flux:table.column>{{ __('general.guard') }}</flux:table.column>
                    <flux:table.column>{{ __('general.permissions_count') }}</flux:table.column>
                    <flux:table.column>{{ __('general.users_count') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->roles as $role)
                        <flux:table.row :key="$role->id">
                            <flux:table.cell variant="strong">
                                <div class="flex items-center gap-2">
                                    <flux:icon.shield variant="outline" class="size-4 text-indigo-500" />
                                    {{ $role->name }}
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ $role->guard_name }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="violet">{{ $role->permissions_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="cyan">{{ $role->users_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $role->created_at->toDynamicFormat('Y/m/d H:i:s') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
@can('user-management.role.edit')
                                    <flux:tooltip content="{{ __('general.role_permissions') }}">
                                        <flux:button size="xs" variant="primary" color="violet" icon="key" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.role.permissions.assign-data', { role: {{ $role->id }} })" />
                                    </flux:tooltip>
@endcan
@can('user-management.role.edit')
                                    <flux:tooltip content="{{ __('general.role_users') }}">
                                        <flux:button size="xs" variant="primary" color="cyan" icon="users" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.role.users.assign-data', { role: {{ $role->id }} })" />
                                    </flux:tooltip>
@endcan
@can('user-management.role.edit')
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.role.edit.assign-data', { role: {{ $role->id }} })" />
                                    </flux:tooltip>
@endcan
@can('user-management.role.delete')
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.role.delete.assign-data', { role: {{ $role->id }} })" />
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

    <livewire:user-management.role.create :key="'role-create'" />
    <livewire:user-management.role.edit :key="'role-edit'" />
    <livewire:user-management.role.delete :key="'role-delete'" />
    <livewire:user-management.role.permissions :key="'role-permissions'" />
    <livewire:user-management.role.users :key="'role-users'" />
</div>
