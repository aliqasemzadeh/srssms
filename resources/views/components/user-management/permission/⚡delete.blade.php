<?php

use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

new class extends Component
{
    public ?Permission $permission = null;

    #[On('panels.administrator.user-management.permission.delete.assign-data')]
    public function assignData(int $permission): void
    {
        $this->permission = Permission::findById($permission);

        Flux::modal('user-management.permission.delete')->show();
    }

    public function delete(): void
    {
        if (! $this->permission) {
            return;
        }

        $this->permission->delete();

        $this->permission = null;

        $this->dispatch('panels.administrator.user-management.permission.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.permission_deleted'));
    }
};
?>

<flux:modal name="user-management.permission.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>

        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($permission)
        <flux:callout icon="key" variant="secondary" inline>
            <flux:callout.heading dir="ltr" class="text-start">{{ $permission->name }}</flux:callout.heading>
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
