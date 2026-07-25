<?php

use App\Models\Phonebook\Group;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?Group $group = null;

    #[On('panels.user.phonebook.group.delete.assign-data')]
    public function assignData(int $group): void
    {
        $this->group = Group::query()->where('user_id', Auth::id())->findOrFail($group);
        Flux::modal('phonebook.group.delete')->show();
    }

    public function delete(): void
    {
        if (! $this->group) {
            return;
        }

        $this->group->delete();
        $this->group = null;
        $this->dispatch('panels.user.phonebook.group.index.refresh');
        $this->dispatch('panels.user.phonebook.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.phonebook_group_deleted'));
    }
};
?>

<flux:modal name="phonebook.group.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>
        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($group)
        <flux:callout icon="users" variant="secondary" inline>
            <flux:callout.heading>{{ $group->name }}</flux:callout.heading>
        </flux:callout>
    @endif

    <div class="flex gap-2">
        <flux:spacer />
        <flux:modal.close>
            <flux:button variant="ghost">{{ __('actions.cancel') }}</flux:button>
        </flux:modal.close>
        <flux:button wire:click="delete" variant="danger" icon="trash" icon:variant="outline">{{ __('actions.delete') }}</flux:button>
    </div>
</flux:modal>
