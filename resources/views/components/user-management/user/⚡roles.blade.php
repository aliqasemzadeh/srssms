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
    public ?User $user = null;

    /** @var array<int|string> */
    public array $role_ids = [];

    #[On('panels.administrator.user-management.user.roles.assign-data')]
    public function assignData(User $user): void
    {
        $this->user = $user;
        $this->role_ids = $user->roles()->pluck('id')->map(fn ($id) => (string) $id)->all();

        Flux::modal('user-roles-modal')->show();
    }

    #[Computed]
    public function roles(): Collection
    {
        return Role::query()->orderBy('name')->get();
    }

    public function save(): void
    {
        if (! $this->user) {
            return;
        }

        $roleNames = Role::query()
            ->whereIn('id', collect($this->role_ids)->filter()->all())
            ->pluck('name')
            ->all();

        $this->user->syncRoles($roleNames);

        $this->dispatch('panels.administrator.user-management.user.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('app.user_roles_updated'));
    }
};
?>

<flux:modal name="user-roles-modal" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('app.user_roles') }}</flux:heading>
        @if ($user)
            <flux:subheading>{{ $user->full_name }}</flux:subheading>
        @endif
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:select
            wire:model="role_ids"
            variant="listbox"
            multiple
            searchable
            label="{{ __('app.select_roles') }}"
            selected-suffix="{{ __('app.selected_suffix') }}"
        >
            @foreach ($this->roles as $role)
                <flux:select.option value="{{ $role->id }}" wire:key="user-role-option-{{ $role->id }}">
                    {{ $role->name }}
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:button type="submit" variant="primary" color="orange" class="w-full">
            {{ __('app.save') }}
        </flux:button>
    </form>
</flux:modal>
