<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;
use Spatie\Permission\Models\Role;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    #[Computed]
    public function roles(): LengthAwarePaginator
    {
        return Role::query()
            ->withCount(['permissions', 'users'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(config('general.per_page', 10));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[On('panels.administrator.user-management.role.index.refresh')]
    public function refresh(): void
    {
        //
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

            <flux:button class="shrink-0" variant="primary" color="teal" icon="plus" wire:click="$dispatch('panels.administrator.user-management.role.create.assign-data')">
                {{ __('actions.create') }} {{ __('general.role') }}
            </flux:button>
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable />
            </div>

            <flux:table :paginate="$this->roles">
                <flux:table.columns>
                    <flux:table.column>{{ __('general.name') }}</flux:table.column>
                    <flux:table.column>{{ __('general.guard') }}</flux:table.column>
                    <flux:table.column>{{ __('general.permissions_count') }}</flux:table.column>
                    <flux:table.column>{{ __('general.users_count') }}</flux:table.column>
                    <flux:table.column>{{ __('general.created_at') }}</flux:table.column>
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
                            <flux:table.cell>{{ Jalalian::fromCarbon($role->created_at)->format('Y/m/d') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.role_permissions') }}">
                                        <flux:button size="xs" variant="primary" color="violet" icon="key" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.role.permissions.assign-data', { role: {{ $role->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.role_users') }}">
                                        <flux:button size="xs" variant="primary" color="cyan" icon="users" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.role.users.assign-data', { role: {{ $role->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.role.edit.assign-data', { role: {{ $role->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.role.delete.assign-data', { role: {{ $role->id }} })" />
                                    </flux:tooltip>
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
