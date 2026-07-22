<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

new class extends Component
{
    public ?Permission $permission = null;

    /** @var array<int> */
    public array $granted_ids = [];

    public string $grantedSearch = '';

    public string $grantableSearch = '';

    public string $pending_action = '';

    public ?int $pending_id = null;

    #[On('panels.administrator.user-management.permission.users.assign-data')]
    public function assignData(int $permission): void
    {
        $this->permission = Permission::findById($permission);
        $this->granted_ids = $this->permission->users()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->reset('grantedSearch', 'grantableSearch', 'pending_action', 'pending_id');

        Flux::modal('user-management.permission.users')->show();
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

    #[Computed]
    public function isGranting(): bool
    {
        return in_array($this->pending_action, ['grant', 'grant-all'], true);
    }

    #[Computed]
    public function pendingLabel(): string
    {
        return match ($this->pending_action) {
            'grant', 'revoke' => User::query()->find($this->pending_id)?->full_name ?? '',
            'grant-all' => __('general.items_count', ['count' => count($this->grantable)]),
            'revoke-all' => __('general.items_count', ['count' => count($this->granted)]),
            default => '',
        };
    }

    public function confirm(string $action, ?int $id = null): void
    {
        if (($action === 'grant-all' && $this->grantable->isEmpty())
            || ($action === 'revoke-all' && $this->granted->isEmpty())) {
            return;
        }

        $this->pending_action = $action;
        $this->pending_id = $id;

        Flux::modal('user-management.permission.users.confirm')->show();
    }

    public function apply(): void
    {
        if (! $this->permission || $this->pending_action === '') {
            return;
        }

        match ($this->pending_action) {
            'grant' => User::query()->find($this->pending_id)?->givePermissionTo($this->permission),
            'revoke' => User::query()->find($this->pending_id)?->revokePermissionTo($this->permission),
            'grant-all' => $this->grantable->each(fn (User $user) => $user->givePermissionTo($this->permission)),
            'revoke-all' => $this->granted->each(fn (User $user) => $user->revokePermissionTo($this->permission)),
        };

        $granting = $this->isGranting;

        $this->granted_ids = $this->permission->users()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->reset('pending_action', 'pending_id');

        unset($this->granted, $this->grantable, $this->isGranting, $this->pendingLabel);

        $this->dispatch('panels.administrator.user-management.permission.index.refresh');

        Flux::modal('user-management.permission.users.confirm')->close();

        Flux::toast(__($granting ? 'general.granted_successfully' : 'general.revoked_successfully'));
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

<div>
    <flux:modal name="user-management.permission.users" class="w-[calc(100vw-2rem)] max-w-none h-[calc(100dvh-2rem)] overflow-y-auto">
        <div class="flex h-full min-h-0 flex-col gap-6">
            <div class="min-w-0 pe-10">
                <div class="flex min-w-0 flex-wrap items-center gap-x-3 gap-y-1">
                    <flux:heading size="lg">{{ __('general.permission_users') }}</flux:heading>
                    @if ($permission)
                        <flux:badge size="sm" color="violet" icon="key" class="max-w-full truncate" dir="ltr">
                            {{ $permission->name }}
                        </flux:badge>
                    @endif
                </div>
                <flux:subheading>{{ __('general.direct_permissions_hint') }}</flux:subheading>
            </div>

            <div class="grid min-h-0 flex-1 grid-cols-1 gap-6 lg:grid-cols-2">
                {{-- Users with this direct permission --}}
                <div class="flex min-h-0 flex-col overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50/40 dark:border-emerald-900 dark:bg-emerald-950/20">
                    <div class="flex items-center justify-between gap-2 border-b border-emerald-200 p-4 dark:border-emerald-900">
                        <div class="flex items-center gap-2">
                            <flux:icon.shield-check variant="outline" class="size-5 text-emerald-500" />
                            <flux:heading size="sm">{{ __('general.granted') }}</flux:heading>
                            <flux:badge size="sm" color="emerald">{{ count($this->granted) }}</flux:badge>
                        </div>
                        <flux:button type="button" size="xs" variant="ghost" color="red" icon="list-x" icon:variant="outline" wire:click="confirm('revoke-all')">
                            {{ __('general.revoke_all') }}
                        </flux:button>
                    </div>

                    <div class="border-b border-emerald-200 p-3 dark:border-emerald-900">
                        <flux:input wire:model.live.debounce.300ms="grantedSearch" size="sm" icon="search" placeholder="{{ __('general.search') }}..." clearable />
                    </div>

                    <div class="min-h-0 max-h-72 flex-1 space-y-2 overflow-y-auto p-3 lg:max-h-none">
                        @forelse ($this->granted as $user)
                            <div class="flex items-center justify-between gap-3 rounded-lg border border-emerald-200 bg-white p-2.5 dark:border-emerald-900 dark:bg-zinc-800" wire:key="perm-user-granted-{{ $user->id }}">
                                <div class="flex min-w-0 items-center gap-3">
                                    <flux:avatar size="sm" name="{{ $user->full_name }}" />
                                    <div class="min-w-0">
                                        <flux:heading size="sm" class="truncate">{{ $user->full_name }}</flux:heading>
                                        <flux:text size="sm" class="truncate">{{ $user->username }}</flux:text>
                                    </div>
                                </div>
                                <flux:tooltip content="{{ __('general.revoke') }}">
                                    <flux:button size="xs" variant="danger" icon="x" icon:variant="outline" wire:click="confirm('revoke', {{ $user->id }})" />
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

                {{-- Users without this direct permission --}}
                <div class="flex min-h-0 flex-col overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between gap-2 border-b border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex items-center gap-2">
                            <flux:icon.users variant="outline" class="size-5 text-cyan-500" />
                            <flux:heading size="sm">{{ __('general.grantable') }}</flux:heading>
                            <flux:badge size="sm" color="cyan">{{ count($this->grantable) }}</flux:badge>
                        </div>
                        <flux:button type="button" size="xs" variant="ghost" color="teal" icon="list-plus" icon:variant="outline" wire:click="confirm('grant-all')">
                            {{ __('general.grant_all') }}
                        </flux:button>
                    </div>

                    <div class="border-b border-zinc-200 p-3 dark:border-zinc-700">
                        <flux:input wire:model.live.debounce.300ms="grantableSearch" size="sm" icon="search" placeholder="{{ __('general.search') }}..." clearable />
                    </div>

                    <div class="min-h-0 max-h-72 flex-1 space-y-2 overflow-y-auto p-3 lg:max-h-none">
                        @forelse ($this->grantable as $user)
                            <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 bg-white p-2.5 dark:border-zinc-700 dark:bg-zinc-800" wire:key="perm-user-grantable-{{ $user->id }}">
                                <div class="flex min-w-0 items-center gap-3">
                                    <flux:avatar size="sm" name="{{ $user->full_name }}" />
                                    <div class="min-w-0">
                                        <flux:heading size="sm" class="truncate">{{ $user->full_name }}</flux:heading>
                                        <flux:text size="sm" class="truncate">{{ $user->username }}</flux:text>
                                    </div>
                                </div>
                                <flux:tooltip content="{{ __('general.grant') }}">
                                    <flux:button size="xs" variant="primary" color="teal" icon="plus" icon:variant="outline" wire:click="confirm('grant', {{ $user->id }})" />
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
        </div>
    </flux:modal>

    {{-- Grant/Revoke confirmation --}}
    <flux:modal name="user-management.permission.users.confirm" class="min-w-[22rem] space-y-6">
        <div>
            <flux:heading size="lg">{{ $this->isGranting ? __('general.grant_confirmation') : __('general.revoke_confirmation') }}</flux:heading>

            <flux:text class="mt-2">
                {{ $this->isGranting ? __('general.grant_warning_message') : __('general.revoke_warning_message') }}
            </flux:text>
        </div>

        <flux:callout icon="user" variant="secondary" inline>
            <flux:callout.heading>{{ $this->pendingLabel }}</flux:callout.heading>
        </flux:callout>

        <div class="flex gap-2">
            <flux:spacer />

            <flux:modal.close>
                <flux:button variant="ghost">{{ __('actions.cancel') }}</flux:button>
            </flux:modal.close>

            @if ($this->isGranting)
                <flux:button wire:click="apply" variant="primary" color="teal" icon="plus" icon:variant="outline">
                    {{ __('general.grant') }}
                </flux:button>
            @else
                <flux:button wire:click="apply" variant="danger" icon="x" icon:variant="outline">
                    {{ __('general.revoke') }}
                </flux:button>
            @endif
        </div>
    </flux:modal>
</div>
