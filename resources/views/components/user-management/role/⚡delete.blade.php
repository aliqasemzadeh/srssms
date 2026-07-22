<?php

use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public ?Role $role = null;

    #[On('panels.administrator.user-management.role.delete.assign-data')]
    public function assignData(int $role): void
    {
        $this->role = Role::findById($role);

        Flux::modal('user-management.role.delete')->show();
    }

    public function delete(): void
    {
        if (! $this->role) {
            return;
        }

        $this->role->delete();

        $this->role = null;

        $this->dispatch('panels.administrator.user-management.role.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.role_deleted'));
    }
};
?>

<flux:modal name="user-management.role.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>

        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($role)
        <flux:callout icon="shield" variant="secondary" inline>
            <flux:callout.heading>{{ $role->name }}</flux:callout.heading>
        </flux:callout>
    @endif

    <div class="flex gap-2">
        <flux:spacer />

        <flux:modal.close>
            <flux:button variant="ghost">{{ __('actions.cancel') }}</flux:button>
        </flux:modal.close>

        <flux:button wire:click="delete" variant="danger" icon="trash" icon:variant="outline">
            {{ __('actions.delete') }}
        </flux:button>
    </div>
</flux:modal>
