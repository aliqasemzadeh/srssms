<?php

use App\Livewire\Concerns\InteractsWithPermissionLabels;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

new class extends Component
{
    use InteractsWithPermissionLabels;
    use WithPagination;

    public string $search = '';

    #[Computed]
    public function permissions(): LengthAwarePaginator
    {
        return Permission::query()
            ->withCount(['roles', 'users'])
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

    #[On('panels.administrator.user-management.permission.index.refresh')]
    public function refresh(): void
    {
        //
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.permissions') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" />
                <flux:breadcrumbs.item>{{ __('general.user_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.permissions') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:button class="shrink-0" variant="primary" color="teal" icon="plus" wire:click="$dispatch('panels.administrator.user-management.permission.create.assign-data')">
                {{ __('actions.create') }} {{ __('general.permission') }}
            </flux:button>
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable />
            </div>

            <flux:table :paginate="$this->permissions">
                <flux:table.columns>
                    <flux:table.column>{{ __('general.name') }}</flux:table.column>
                    <flux:table.column>{{ __('general.guard') }}</flux:table.column>
                    <flux:table.column>{{ __('general.roles_count') }}</flux:table.column>
                    <flux:table.column>{{ __('general.users_count') }}</flux:table.column>
                    <flux:table.column>{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->permissions as $permission)
                        <flux:table.row :key="$permission->id">
                            <flux:table.cell variant="strong">
                                <div class="flex items-center gap-2">
                                    <flux:icon.key variant="outline" class="size-4 text-violet-500" />
                                    <div>
                                        {{ $this->permissionLabel($permission->name) }}
                                        <flux:text size="sm" dir="ltr" class="text-start">{{ $permission->name }}</flux:text>
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ $permission->guard_name }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="indigo">{{ $permission->roles_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="cyan">{{ $permission->users_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $permission->created_at->toDynamicFormat('Y/m/d H:i:s') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.permission_roles') }}">
                                        <flux:button size="xs" variant="primary" color="indigo" icon="shield" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.permission.roles.assign-data', { permission: {{ $permission->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.permission_users') }}">
                                        <flux:button size="xs" variant="primary" color="cyan" icon="users" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.permission.users.assign-data', { permission: {{ $permission->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.permission.edit.assign-data', { permission: {{ $permission->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.permission.delete.assign-data', { permission: {{ $permission->id }} })" />
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:user-management.permission.create :key="'permission-create'" />
    <livewire:user-management.permission.edit :key="'permission-edit'" />
    <livewire:user-management.permission.delete :key="'permission-delete'" />
    <livewire:user-management.permission.roles :key="'permission-roles'" />
    <livewire:user-management.permission.users :key="'permission-users'" />
</div>
