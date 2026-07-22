<?php

use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?User $user = null;

    #[On('panels.administrator.user-management.user.delete.assign-data')]
    public function assignData(User $user): void
    {
        $this->user = $user;

        Flux::modal('user-delete-modal')->show();
    }

    public function delete(): void
    {
        if (! $this->user) {
            return;
        }

        $this->user->delete();

        $this->user = null;

        $this->dispatch('panels.administrator.user-management.user.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.user_deleted'));
    }
};
?>

<flux:modal name="user-delete-modal" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.delete') }} {{ __('general.user') }}</flux:heading>
        <flux:subheading>
            {{ __('general.are_you_sure') }}
        </flux:subheading>
    </div>

    @if ($user)
        <flux:callout icon="user" variant="secondary" inline>
            {{ $user->full_name }} ({{ $user->username }})
        </flux:callout>
    @endif

    <flux:button wire:click="delete" variant="primary" color="red" class="w-full">
        {{ __('actions.delete') }}
    </flux:button>
</flux:modal>
