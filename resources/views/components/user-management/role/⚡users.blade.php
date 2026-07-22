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

    /** @var array<int|string> */
    public array $user_ids = [];

    public string $userSearch = '';

    #[On('panels.administrator.user-management.role.users.assign-data')]
    public function assignData(int $role): void
    {
        $this->role = Role::findById($role);
        $this->user_ids = $this->role->users()->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->userSearch = '';

        Flux::modal('role-users-modal')->show();
    }

    #[Computed]
    public function users(): Collection
    {
        $selectedIds = collect($this->user_ids)->filter()->map(fn ($id) => (int) $id)->all();

        $users = User::query()
            ->when($this->userSearch !== '', function ($query) {
                $search = $this->userSearch;

                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->orderBy('first_name')
            ->limit(50)
            ->get();

        if ($selectedIds !== []) {
            $missing = User::query()
                ->whereIn('id', $selectedIds)
                ->whereNotIn('id', $users->pluck('id'))
                ->get();

            $users = $users->concat($missing)->unique('id')->values();
        }

        return $users;
    }

    public function save(): void
    {
        if (! $this->role) {
            return;
        }

        $selectedIds = collect($this->user_ids)->filter()->map(fn ($id) => (int) $id)->unique()->values();
        $currentIds = $this->role->users()->pluck('id')->map(fn ($id) => (int) $id);

        foreach ($selectedIds->diff($currentIds) as $userId) {
            User::query()->find($userId)?->assignRole($this->role);
        }

        foreach ($currentIds->diff($selectedIds) as $userId) {
            User::query()->find($userId)?->removeRole($this->role);
        }

        $this->dispatch('panels.administrator.user-management.role.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.role_users_updated'));
    }
};
?>

<flux:modal name="role-users-modal" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.role_users') }}</flux:heading>
        @if ($role)
            <flux:subheading>{{ $role->name }}</flux:subheading>
        @endif
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:select
            wire:model="user_ids"
            variant="listbox"
            multiple
            searchable
            :filter="false"
            label="{{ __('general.select_users') }}"
            selected-suffix="{{ __('general.selected_suffix') }}"
        >
            <x-slot name="search">
                <flux:select.search wire:model.live.debounce.300ms="userSearch" placeholder="{{ __('general.search') }}..." />
            </x-slot>

            @foreach ($this->users as $user)
                <flux:select.option value="{{ $user->id }}" wire:key="role-user-option-{{ $user->id }}">
                    {{ $user->full_name }} ({{ $user->username }})
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
