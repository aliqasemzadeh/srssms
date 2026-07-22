<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

new class extends Component
{
    public ?User $user = null;

    /** @var array<int> */
    public array $permission_ids = [];

    public string $permissionSearch = '';

    #[On('panels.administrator.user-management.user.permissions.assign-data')]
    public function assignData(User $user): void
    {
        $this->user = $user;
        $this->permission_ids = $user->getDirectPermissions()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->permissionSearch = '';

        Flux::modal('user-permissions-modal')->show();
    }

    #[Computed]
    public function permissionGroups(): Collection
    {
        $search = trim($this->permissionSearch);

        return Permission::query()
            ->orderBy('name')
            ->get()
            ->filter(function (Permission $permission) use ($search) {
                if ($search === '') {
                    return true;
                }

                $label = $this->permissionLabel($permission->name);

                return str_contains(Str::lower($permission->name), Str::lower($search))
                    || str_contains(Str::lower($label), Str::lower($search));
            })
            ->groupBy(fn (Permission $permission) => Str::beforeLast($permission->name, '.') ?: $permission->name);
    }

    public function permissionLabel(string $name): string
    {
        $key = 'permissions.'.$name;

        return Lang::has($key) ? __($key) : $name;
    }

    public function selectGroup(string $group): void
    {
        $ids = $this->permissionGroups->get($group)?->pluck('id')->map(fn ($id) => (int) $id)->all() ?? [];

        $this->permission_ids = collect($this->permission_ids)->merge($ids)->unique()->values()->all();
    }

    public function deselectGroup(string $group): void
    {
        $ids = $this->permissionGroups->get($group)?->pluck('id')->map(fn ($id) => (int) $id)->all() ?? [];

        $this->permission_ids = collect($this->permission_ids)->reject(fn ($id) => in_array($id, $ids, true))->values()->all();
    }

    public function save(): void
    {
        if (! $this->user) {
            return;
        }

        $this->user->syncPermissions($this->permission_ids);

        $this->dispatch('panels.administrator.user-management.user.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.user_permissions_updated'));
    }
};
?>

<flux:modal name="user-permissions-modal" flyout position="right" class="space-y-6 md:w-lg">
    <div>
        <flux:heading size="lg">{{ __('general.user_permissions') }}</flux:heading>
        @if ($user)
            <flux:subheading>{{ $user->full_name }}</flux:subheading>
        @endif
    </div>

    <flux:input wire:model.live.debounce.300ms="permissionSearch" icon="search" placeholder="{{ __('actions.search') }}..." />

    <form wire:submit="save" class="space-y-6">
        <div class="space-y-4 max-h-[70vh] overflow-y-auto pe-1">
            @forelse ($this->permissionGroups as $group => $permissions)
                <div class="space-y-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700" wire:key="user-perm-group-{{ $group }}">
                    <div class="flex items-center justify-between gap-2">
                        <flux:heading size="sm">{{ $group }}</flux:heading>
                        <div class="flex gap-1">
                            <flux:button type="button" size="xs" variant="ghost" color="teal" wire:click="selectGroup('{{ $group }}')">
                                {{ __('actions.select_all') }}
                            </flux:button>
                            <flux:button type="button" size="xs" variant="ghost" color="zinc" wire:click="deselectGroup('{{ $group }}')">
                                {{ __('actions.deselect_all') }}
                            </flux:button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        @foreach ($permissions as $permission)
                            <flux:checkbox
                                wire:model="permission_ids"
                                value="{{ $permission->id }}"
                                label="{{ $this->permissionLabel($permission->name) }}"
                                description="{{ $permission->name }}"
                                wire:key="user-perm-{{ $permission->id }}"
                            />
                        @endforeach
                    </div>
                </div>
            @empty
                <flux:text>{{ __('general.search') }}...</flux:text>
            @endforelse
        </div>

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
