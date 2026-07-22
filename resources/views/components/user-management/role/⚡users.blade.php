<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public ?Role $role = null;

    /** @var array<int> */
    public array $granted_ids = [];

    public string $grantedSearch = '';

    public string $grantableSearch = '';

    #[On('panels.administrator.user-management.role.users.assign-data')]
    public function assignData(int $role): void
    {
        $this->role = Role::findById($role);
        $this->granted_ids = $this->role->users()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->reset('grantedSearch', 'grantableSearch');

        Flux::modal('user-management.role.users')->show();
    }

    #[Computed]
    public function granted(): Collection
    {
        if ($this->granted_ids === []) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $this->granted_ids)
            ->tap(fn ($query) => $this->applySearch($query, $this->grantedSearch))
            ->orderBy('first_name')
            ->get();
    }

    #[Computed]
    public function grantable(): Collection
    {
        return User::query()
            ->whereNotIn('id', $this->granted_ids)
            ->tap(fn ($query) => $this->applySearch($query, $this->grantableSearch))
            ->orderBy('first_name')
            ->limit(50)
            ->get();
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
        if (! $this->role) {
            return;
        }

        $selectedIds = collect($this->granted_ids)->unique()->values();
        $currentIds = $this->role->users()->pluck('id')->map(fn ($id) => (int) $id);

        User::query()
            ->whereIn('id', $selectedIds->diff($currentIds))
            ->get()
            ->each(fn (User $user) => $user->assignRole($this->role));

        User::query()
            ->whereIn('id', $currentIds->diff($selectedIds))
            ->get()
            ->each(fn (User $user) => $user->removeRole($this->role));

        $this->dispatch('panels.administrator.user-management.role.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.role_users_updated'));
    }

    protected function applySearch($query, string $search): void
    {
        $search = trim($search);

        if ($search === '') {
            return;
        }

        $query->where(function ($query) use ($search) {
            $query->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%");
        });
    }
};
?>

<flux:modal name="user-management.role.users" class="w-[calc(100vw-2rem)] max-w-none h-[calc(100dvh-2rem)] overflow-y-auto">
    <div class="flex h-full min-h-0 flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4 pe-10">
            <div>
                <flux:heading size="lg">{{ __('general.role_users') }}</flux:heading>
                <flux:subheading>{{ __('general.granted') }} / {{ __('general.grantable') }}</flux:subheading>
            </div>

            @if ($role)
                <flux:callout icon="shield" variant="secondary" inline class="w-auto">
                    <flux:callout.heading>{{ $role->name }}</flux:callout.heading>
                </flux:callout>
            @endif
        </div>

        <div class="grid min-h-0 flex-1 grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Users with this role --}}
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
                    @forelse ($this->granted as $user)
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-emerald-200 bg-white p-2.5 dark:border-emerald-900 dark:bg-zinc-800" wire:key="role-user-granted-{{ $user->id }}">
                            <div class="flex min-w-0 items-center gap-3">
                                <flux:avatar size="sm" name="{{ $user->full_name }}" />
                                <div class="min-w-0">
                                    <flux:heading size="sm" class="truncate">{{ $user->full_name }}</flux:heading>
                                    <flux:text size="sm" class="truncate">{{ $user->username }}</flux:text>
                                </div>
                            </div>
                            <flux:tooltip content="{{ __('general.revoke') }}">
                                <flux:button size="xs" variant="danger" icon="x" icon:variant="outline" wire:click="revoke({{ $user->id }})" />
                            </flux:tooltip>
                        </div>
                    @empty
                        <div class="flex h-full min-h-40 flex-col items-center justify-center gap-2 text-center">
                            <flux:icon.users variant="outline" class="size-8 text-zinc-300 dark:text-zinc-600" />
                            <flux:text>{{ trim($grantedSearch) !== '' ? __('general.no_results_found') : __('general.nothing_granted_yet') }}</flux:text>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Users without this role --}}
            <div class="flex min-h-0 flex-col overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between gap-2 border-b border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="flex items-center gap-2">
                        <flux:icon.users variant="outline" class="size-5 text-cyan-500" />
                        <flux:heading size="sm">{{ __('general.grantable') }}</flux:heading>
                        <flux:badge size="sm" color="cyan">{{ count($this->grantable) }}</flux:badge>
                    </div>
                    <flux:button type="button" size="xs" variant="ghost" color="teal" icon="list-plus" icon:variant="outline" wire:click="grantAll">
                        {{ __('general.grant_all') }}
                    </flux:button>
                </div>

                <div class="border-b border-zinc-200 p-3 dark:border-zinc-700">
                    <flux:input wire:model.live.debounce.300ms="grantableSearch" size="sm" icon="search" placeholder="{{ __('general.search') }}..." clearable />
                </div>

                <div class="min-h-0 max-h-72 flex-1 space-y-2 overflow-y-auto p-3 lg:max-h-none">
                    @forelse ($this->grantable as $user)
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 bg-white p-2.5 dark:border-zinc-700 dark:bg-zinc-800" wire:key="role-user-grantable-{{ $user->id }}">
                            <div class="flex min-w-0 items-center gap-3">
                                <flux:avatar size="sm" name="{{ $user->full_name }}" />
                                <div class="min-w-0">
                                    <flux:heading size="sm" class="truncate">{{ $user->full_name }}</flux:heading>
                                    <flux:text size="sm" class="truncate">{{ $user->username }}</flux:text>
                                </div>
                            </div>
                            <flux:tooltip content="{{ __('general.grant') }}">
                                <flux:button size="xs" variant="primary" color="teal" icon="plus" icon:variant="outline" wire:click="grant({{ $user->id }})" />
                            </flux:tooltip>
                        </div>
                    @empty
                        <div class="flex h-full min-h-40 flex-col items-center justify-center gap-2 text-center">
                            <flux:icon.users variant="outline" class="size-8 text-zinc-300 dark:text-zinc-600" />
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
