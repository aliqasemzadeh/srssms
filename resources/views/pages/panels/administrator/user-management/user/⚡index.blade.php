<?php

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('mobile', 'like', "%{$this->search}%")
                        ->orWhere('username', 'like', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate(config('general.per_page', 10));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[On('panels.administrator.user-management.user.index.refresh')]
    public function refresh(): void
    {
        //
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.users') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" />
                <flux:breadcrumbs.item>{{ __('general.users') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:button variant="primary" color="teal" icon="plus" wire:click="$dispatch('panels.administrator.user-management.user.create.assign-data')">
                {{ __('actions.create') }} {{ __('general.user') }}
            </flux:button>
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." />
            </div>

            <flux:table :paginate="$this->users">
                <flux:table.columns>
                    <flux:table.column>{{ __('general.first_name') }}</flux:table.column>
                    <flux:table.column>{{ __('general.last_name') }}</flux:table.column>
                    <flux:table.column>{{ __('general.mobile') }}</flux:table.column>
                    <flux:table.column>{{ __('general.email') }}</flux:table.column>
                    <flux:table.column>{{ __('general.username') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->users as $user)
                        <flux:table.row :key="$user->id">
                            <flux:table.cell>{{ $user->first_name }}</flux:table.cell>
                            <flux:table.cell>{{ $user->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ $user->mobile }}</flux:table.cell>
                            <flux:table.cell>{{ $user->email }}</flux:table.cell>
                            <flux:table.cell>{{ $user->username }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.user.edit.assign-data', { user: {{ $user->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.user_roles') }}">
                                        <flux:button size="xs" variant="primary" color="indigo" icon="shield" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.user.roles.assign-data', { user: {{ $user->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.user_permissions') }}">
                                        <flux:button size="xs" variant="primary" color="violet" icon="key" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.user.permissions.assign-data', { user: {{ $user->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="primary" color="red" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.user.delete.assign-data', { user: {{ $user->id }} })" />
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:user-management.user.create :key="'user-create'" />
    <livewire:user-management.user.edit :key="'user-edit'" />
    <livewire:user-management.user.delete :key="'user-delete'" />
    <livewire:user-management.user.roles :key="'user-roles'" />
    <livewire:user-management.user.permissions :key="'user-permissions'" />
</div>
