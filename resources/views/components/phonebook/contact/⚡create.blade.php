<?php

use App\Enums\Phonebook\ContactGenderEnum;
use App\Enums\Phonebook\ContactPersonTypeEnum;
use App\Livewire\Forms\Phonebook\ContactForm;
use App\Models\Phonebook\Contact;
use App\Models\Phonebook\Group;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Tags\Tag;

new class extends Component
{
    public ContactForm $form;

    public string $tagSearch = '';

    #[On('panels.user.phonebook.contact.create.assign-data')]
    public function assignData(?int $groupId = null): void
    {
        $this->form->reset();
        $this->form->group_ids = $groupId ? [$groupId] : [];
        $this->form->tags = [];
        $this->tagSearch = '';
        $this->resetValidation();
        unset($this->groups, $this->availableTags);

        Flux::modal('phonebook.contact.create')->show();
    }

    #[Computed]
    public function groups(): Collection
    {
        return Group::query()->where('user_id', Auth::id())->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function availableTags(): Collection
    {
        return Tag::query()
            ->where('type', Contact::tagTypeFor(Auth::user()))
            ->orderBy('order_column')
            ->get();
    }

    public function createTag(): void
    {
        $name = trim($this->tagSearch);

        if ($name === '' || mb_strlen($name) < 1) {
            return;
        }

        $tag = Tag::findOrCreate($name, Contact::tagTypeFor(Auth::user()));
        $tagName = (string) $tag->name;

        if (! in_array($tagName, $this->form->tags, true)) {
            $this->form->tags[] = $tagName;
        }

        $this->tagSearch = '';
        unset($this->availableTags);

        Flux::toast(__('general.phonebook_tag_created'));
    }

    public function save(): void
    {
        $this->form->store();
        $this->form->reset();
        $this->tagSearch = '';
        $this->dispatch('panels.user.phonebook.index.refresh');
        $this->dispatch('panels.user.phonebook.group.view.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.contact_created'));
    }
};
?>

<flux:modal name="phonebook.contact.create" flyout position="right" class="md:w-96 space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.create') }} {{ __('general.contact') }}</flux:heading>
        <flux:subheading>{{ __('general.phonebook') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-4">
        <flux:input wire:model="form.first_name" label="{{ __('general.first_name') }} *" icon="user" />
        <flux:input wire:model="form.last_name" label="{{ __('general.last_name') }}" icon="user" />
        <flux:input wire:model="form.mobile" label="{{ __('general.mobile') }} *" icon="phone" dir="ltr" />
        <flux:input wire:model="form.company" label="{{ __('general.company') }}" />

        <flux:select wire:model="form.gender" variant="listbox" searchable label="{{ __('general.gender') }}" clearable>
            @foreach (ContactGenderEnum::options() as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model="form.person_type" variant="listbox" searchable label="{{ __('general.person_type') }}" clearable>
            @foreach (ContactPersonTypeEnum::options() as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        @if (app()->getLocale() === 'fa')
            <x-persian-date-picker wire:model="form.birth_date" label="{{ __('general.birth_date') }}" />
            <x-persian-date-picker wire:model="form.marriage_date" label="{{ __('general.marriage_date') }}" />
        @else
            <flux:input wire:model="form.birth_date" type="date" label="{{ __('general.birth_date') }}" />
            <flux:input wire:model="form.marriage_date" type="date" label="{{ __('general.marriage_date') }}" />
        @endif

        <flux:textarea wire:model="form.address" label="{{ __('general.address') }}" rows="2" />
        <flux:input wire:model="form.postal_code" label="{{ __('general.postal_code') }}" dir="ltr" />
        <flux:input wire:model="form.national_code" label="{{ __('general.national_code') }}" dir="ltr" />
        <flux:input wire:model="form.economic_code" label="{{ __('general.economic_code') }}" dir="ltr" />

        <flux:select wire:model="form.group_ids" variant="listbox" searchable multiple label="{{ __('general.phonebook_groups') }}" clearable>
            @foreach ($this->groups as $group)
                <flux:select.option value="{{ $group->id }}">{{ $group->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:pillbox wire:model="form.tags" variant="combobox" multiple label="{{ __('general.phonebook_tags') }}">
            <x-slot name="input">
                <flux:pillbox.input wire:model="tagSearch" placeholder="{{ __('general.search_phonebook_tags') }}" />
            </x-slot>

            @foreach ($this->availableTags as $tag)
                <flux:pillbox.option value="{{ $tag->name }}" :wire:key="$tag->id">{{ $tag->name }}</flux:pillbox.option>
            @endforeach

            <flux:pillbox.option.create wire:click="createTag" min-length="1">
                {{ __('general.create_phonebook_tag') }} "<span wire:text="tagSearch"></span>"
            </flux:pillbox.option.create>
        </flux:pillbox>

        <flux:button type="submit" variant="primary" color="teal" class="w-full">{{ __('actions.save') }}</flux:button>
    </form>
</flux:modal>
