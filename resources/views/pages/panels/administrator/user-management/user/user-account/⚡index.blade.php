<?php

use App\Enums\UserAccountTypeEnum;
use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public User $user;

    public string $search = '';

    public string $type = '';

    public string $status = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function userAccounts(): LengthAwarePaginator
    {
        $allowedSorts = ['account_number', 'account_owner', 'type', 'status', 'created_at'];

        $sortBy = in_array($this->sortBy, $allowedSorts, true) ? $this->sortBy : 'created_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return UserAccount::query()
            ->where('user_id', $this->user->id)
            ->with([
                'currency' => fn ($query) => $query->withTrashed(),
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('account_number', 'like', "%{$this->search}%")
                        ->orWhere('account_owner', 'like', "%{$this->search}%");
                });
            })
            ->when($this->type, fn ($query) => $query->where('type', $this->type))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->orderBy($sortBy, $sortDirection)
            ->paginate(config('general.per_page', 10));
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = $column === 'created_at' ? 'desc' : 'asc';
        }

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'type',
            'status',
        ]);

        $this->resetPage();
    }

    #[On('panels.administrator.user-management.user.user-account.index.refresh')]
    public function refresh(): void
    {
        unset($this->userAccounts);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.user_accounts') }} - {{ $user->full_name }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.user_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.user-management.user.index') }}" wire:navigate>{{ __('general.users') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $user->full_name }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.user_accounts') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            @can('user-management.user.edit')
            <div class="sm:hidden shrink-0">
                <flux:dropdown align="end">
                    <flux:button icon:trailing="chevron-down">{{ __('general.actions') }}</flux:button>
                    <flux:menu>
                        <flux:menu.item
                            icon="plus"
                            wire:click="$dispatch('panels.administrator.user-management.user.user-account.create.assign-data')"
                        >
                            {{ __('actions.create') }} {{ __('general.user_account') }}
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>

            <div class="hidden items-center gap-2 sm:flex shrink-0">
                <flux:button
                    variant="primary"
                    color="teal"
                    icon="plus"
                    wire:click="$dispatch('panels.administrator.user-management.user.user-account.create.assign-data')"
                >
                    {{ __('actions.create') }} {{ __('general.user_account') }}
                </flux:button>
            </div>
            @endcan
        </div>

        <flux:card class="space-y-4">
            @php
                $activeFilters = collect([
                    $type,
                    $status,
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
                        <flux:select wire:model.live="type" variant="listbox" searchable placeholder="{{ __('general.type') }}..." clearable>
                            @foreach (UserAccountTypeEnum::cases() as $accountType)
                                <flux:select.option value="{{ $accountType->value }}">{{ $accountType->label() }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="status" variant="listbox" searchable placeholder="{{ __('general.status') }}..." clearable>
                            <flux:select.option value="{{ UserAccount::STATUS_PENDING }}">{{ __('general.statuses.pending') }}</flux:select.option>
                            <flux:select.option value="{{ UserAccount::STATUS_APPROVED }}">{{ __('general.statuses.approved') }}</flux:select.option>
                            <flux:select.option value="{{ UserAccount::STATUS_REJECTED }}">{{ __('general.statuses.rejected') }}</flux:select.option>
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

            <flux:table :paginate="$this->userAccounts">
                <flux:table.columns>
                    <flux:table.column>{{ __('general.currency') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'type'" :direction="$sortDirection" wire:click="sort('type')">{{ __('general.type') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'account_number'" :direction="$sortDirection" wire:click="sort('account_number')">{{ __('general.account_number') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'account_owner'" :direction="$sortDirection" wire:click="sort('account_owner')">{{ __('general.account_owner') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection" wire:click="sort('status')">{{ __('general.status') }}</flux:table.column>
                    <flux:table.column>{{ __('general.is_active') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->userAccounts as $userAccount)
                        @php
                            $currency = $userAccount->currency;
                            $statusColor = match ($userAccount->status) {
                                UserAccount::STATUS_APPROVED => 'green',
                                UserAccount::STATUS_REJECTED => 'red',
                                default => 'amber',
                            };
                        @endphp
                        <flux:table.row :key="$userAccount->id">
                            <flux:table.cell>
                                @if ($currency && ! $currency->trashed())
                                    <span dir="ltr">{{ $currency->symbol }}</span> — {{ $currency->name }}
                                @else
                                    <span class="text-zinc-400">{{ __('general.deleted') }}</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $userAccount->type instanceof UserAccountTypeEnum ? $userAccount->type->label() : $userAccount->type }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <span dir="ltr">{{ $userAccount->account_number }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $userAccount->account_owner ?: '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $statusColor }}">
                                    {{ __('general.statuses.'.$userAccount->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $userAccount->is_active ? 'green' : 'red' }}">
                                    {{ $userAccount->is_active ? __('general.active') : __('general.inactive') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $userAccount->created_at->toDynamicFormat('Y/m/d H:i:s') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    @can('user-management.user.edit')
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.user.user-account.edit.assign-data', { userAccount: {{ $userAccount->id }} })" />
                                    </flux:tooltip>
                                    @endcan
                                    @can('user-management.user.edit')
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="primary" color="red" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.user.user-account.delete.assign-data', { userAccount: {{ $userAccount->id }} })" />
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

    <livewire:user-management.user.user-account.create :user="$user" :key="'user-account-create-'.$user->id" />
    <livewire:user-management.user.user-account.edit :key="'user-account-edit-'.$user->id" />
    <livewire:user-management.user.user-account.delete :key="'user-account-delete-'.$user->id" />
</div>
