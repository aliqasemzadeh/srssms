<?php

use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public ?Permission $permission = null;

    /** @var array<int> */
    public array $granted_ids = [];

    public string $grantedSearch = '';

    public string $grantableSearch = '';

    #[On('panels.administrator.user-management.permission.roles.assign-data')]
    public function assignData(int $permission): void
    {
        $this->permission = Permission::findById($permission);
        $this->granted_ids = $this->permission->roles()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->reset('grantedSearch', 'grantableSearch');

        Flux::modal('user-management.permission.roles')->show();
    }

    #[Computed]
    public function allRoles(): Collection
    {
        return Role::query()->orderBy('name')->get();
    }

    #[Computed]
    public function granted(): Collection
    {
        return $this->allRoles
            ->whereIn('id', $this->granted_ids)
            ->filter(fn (Role $role) => $this->matches($role, $this->grantedSearch))
            ->values();
    }

    #[Computed]
    public function grantable(): Collection
    {
        return $this->allRoles
            ->whereNotIn('id', $this->granted_ids)
            ->filter(fn (Role $role) => $this->matches($role, $this->grantableSearch))
            ->values();
    }

    public function grant(int $id): void
    {
        $this->granted_ids = collect($this->granted_ids)->push($id)->unique()->values()->all();
    }

    public function revoke(int $id): void
    {
        $this->granted_ids = collect($this->granted_ids)->reject(fn ($granted) => $granted === $id)->values()->all();
    }

    public function grantAll(): void
    {
        $this->granted_ids = collect($this->granted_ids)->merge($this->grantable->pluck('id'))->unique()->values()->all();
    }

    public function revokeAll(): void
    {
        $ids = $this->granted->pluck('id')->all();

        $this->granted_ids = collect($this->granted_ids)->reject(fn ($granted) => in_array($granted, $ids, true))->values()->all();
    }

    public function save(): void
    {
        if (! $this->permission) {
            return;
        }

        $this->permission->syncRoles($this->granted_ids);

        $this->dispatch('panels.administrator.user-management.permission.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.permission_roles_updated'));
    }

    protected function matches(Role $role, string $search): bool
    {
        $search = trim($search);

        return $search === '' || str_contains(Str::lower($role->name), Str::lower($search));
    }
};
?>

<flux:modal name="user-management.permission.roles" class="w-[calc(100vw-2rem)] max-w-none h-[calc(100dvh-2rem)] overflow-y-auto">
    <div class="flex h-full min-h-0 flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4 pe-10">
            <div>
                <flux:heading size="lg">{{ __('general.permission_roles') }}</flux:heading>
                <flux:subheading>{{ __('general.granted') }} / {{ __('general.grantable') }}</flux:subheading>
            </div>

            @if ($permission)
                <flux:callout icon="key" variant="secondary" inline class="w-auto">
                    <flux:callout.heading dir="ltr" class="text-start">{{ $permission->name }}</flux:callout.heading>
                </flux:callout>
            @endif
        </div>

        <div class="grid min-h-0 flex-1 grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Roles that have this permission --}}
            <div class="flex min-h-0 flex-col overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50/40 dark:border-emerald-900 dark:bg-emerald-950/20">
                <div class="flex items-center justify-between gap-2 border-b border-emerald-200 p-4 dark:border-emerald-900">
                    <div class="flex items-center gap-2">
                        <flux:icon.shield-check variant="outline" class="size-5 text-emerald-500" />
                        <flux:heading size="sm">{{ __('general.granted') }}</flux:heading>
                        <flux:badge size="sm" color="emerald">{{ count($this->granted) }}</flux:badge>
                    </div>
                    <flux:button type="button" size="xs" variant="ghost" color="red" icon="list-x" icon:variant="outline" wire:click="revokeAll">
                        {{ __('general.revoke_all') }}
                    </flux:button>
                </div>

                <div class="border-b border-emerald-200 p-3 dark:border-emerald-900">
                    <flux:input wire:model.live.debounce.300ms="grantedSearch" size="sm" icon="search" placeholder="{{ __('general.search') }}..." clearable />
                </div>

                <div class="min-h-0 max-h-72 flex-1 space-y-2 overflow-y-auto p-3 lg:max-h-none">
                    @forelse ($this->granted as $role)
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-emerald-200 bg-white p-2.5 dark:border-emerald-900 dark:bg-zinc-800" wire:key="perm-role-granted-{{ $role->id }}">
                            <div class="flex min-w-0 items-center gap-2">
                                <flux:icon.shield variant="outline" class="size-4 shrink-0 text-indigo-500" />
                                <flux:heading size="sm" class="truncate">{{ $role->name }}</flux:heading>
                            </div>
                            <flux:tooltip content="{{ __('general.revoke') }}">
                                <flux:button size="xs" variant="danger" icon="x" icon:variant="outline" wire:click="revoke({{ $role->id }})" />
                            </flux:tooltip>
                        </div>
                    @empty
                        <div class="flex h-full min-h-40 flex-col items-center justify-center gap-2 text-center">
                            <flux:icon.shield-check variant="outline" class="size-8 text-zinc-300 dark:text-zinc-600" />
                            <flux:text>{{ trim($grantedSearch) !== '' ? __('general.no_results_found') : __('general.nothing_granted_yet') }}</flux:text>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Roles without this permission --}}
            <div class="flex min-h-0 flex-col overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between gap-2 border-b border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="flex items-center gap-2">
                        <flux:icon.shield variant="outline" class="size-5 text-indigo-500" />
                        <flux:heading size="sm">{{ __('general.grantable') }}</flux:heading>
                        <flux:badge size="sm" color="indigo">{{ count($this->grantable) }}</flux:badge>
                    </div>
                    <flux:button type="button" size="xs" variant="ghost" color="teal" icon="list-plus" icon:variant="outline" wire:click="grantAll">
                        {{ __('general.grant_all') }}
                    </flux:button>
                </div>

                <div class="border-b border-zinc-200 p-3 dark:border-zinc-700">
                    <flux:input wire:model.live.debounce.300ms="grantableSearch" size="sm" icon="search" placeholder="{{ __('general.search') }}..." clearable />
                </div>

                <div class="min-h-0 max-h-72 flex-1 space-y-2 overflow-y-auto p-3 lg:max-h-none">
                    @forelse ($this->grantable as $role)
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 bg-white p-2.5 dark:border-zinc-700 dark:bg-zinc-800" wire:key="perm-role-grantable-{{ $role->id }}">
                            <div class="flex min-w-0 items-center gap-2">
                                <flux:icon.shield variant="outline" class="size-4 shrink-0 text-indigo-500" />
                                <flux:heading size="sm" class="truncate">{{ $role->name }}</flux:heading>
                            </div>
                            <flux:tooltip content="{{ __('general.grant') }}">
                                <flux:button size="xs" variant="primary" color="teal" icon="plus" icon:variant="outline" wire:click="grant({{ $role->id }})" />
                            </flux:tooltip>
                        </div>
                    @empty
                        <div class="flex h-full min-h-40 flex-col items-center justify-center gap-2 text-center">
                            <flux:icon.badge-check variant="outline" class="size-8 text-zinc-300 dark:text-zinc-600" />
                            <flux:text>{{ trim($grantableSearch) !== '' ? __('general.no_results_found') : __('general.everything_granted') }}</flux:text>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <flux:button wire:click="save" variant="primary" color="teal" icon="badge-check" icon:variant="outline" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </div>
</flux:modal>
