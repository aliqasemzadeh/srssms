<?php

use App\Models\Phonebook\Contact;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Tags\Tag;

new class extends Component
{
    public ?Tag $tag = null;

    #[On('panels.user.phonebook.tag.delete.assign-data')]
    public function assignData(int $tag): void
    {
        $this->tag = Tag::query()
            ->where('type', Contact::tagTypeFor(Auth::user()))
            ->findOrFail($tag);

        Flux::modal('phonebook.tag.delete')->show();
    }

    public function delete(): void
    {
        if (! $this->tag) {
            return;
        }

        $this->tag->delete();
        $this->tag = null;
        $this->dispatch('panels.user.phonebook.tag.index.refresh');
        $this->dispatch('panels.user.phonebook.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.phonebook_tag_deleted'));
    }
};
?>

<flux:modal name="phonebook.tag.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>
        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($tag)
        <flux:callout icon="tags" variant="secondary" inline>
            <flux:callout.heading>{{ $tag->name }}</flux:callout.heading>
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
