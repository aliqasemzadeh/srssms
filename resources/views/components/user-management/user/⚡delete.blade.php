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
        $this->user->delete();

        $this->dispatch('panels.administrator.user-management.user.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.user_deleted'));
    }
};
?>

<flux:modal name="user-delete-modal" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_user') }}</flux:heading>
        <flux:subheading>
            {{ __('general.are_you_sure') }}
        </flux:subheading>
    </div>

    @if($user)
        <flux:callout icon="user" variant="secondary" inline>
            {{ $user->full_name }} ({{ $user->username }})
        </flux:callout>
    @endif

    <div class="flex">
        <flux:spacer />
        <flux:button wire:click="delete" variant="primary" color="red" class="w-full">
            {{ __('general.delete') }}
        </flux:button>
    </div>
</flux:modal>
