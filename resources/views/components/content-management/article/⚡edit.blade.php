<?php

use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Livewire\Forms\Content\ArticleForm;
use App\Models\Content\Article;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Tags\Tag;

new class extends Component
{
    use AuthorizesAdministratorPermissions;

    public ArticleForm $form;

    public string $tagSearch = '';

    #[On('panels.administrator.content-management.article.edit.assign-data')]
    public function assignData(int $article): void
    {
        $this->authorizePermission('content-management.article.edit');

        $this->form->setModel(Article::query()->findOrFail($article));
        $this->tagSearch = '';
        $this->resetValidation();
        unset($this->availableTags);

        Flux::modal('content-management.article.edit')->show();
    }

    #[Computed]
    public function availableTags(): Collection
    {
        return Tag::query()
            ->where('type', Article::tagType())
            ->orderBy('order_column')
            ->get();
    }

    public function createTag(): void
    {
        $name = trim($this->tagSearch);

        if ($name === '') {
            return;
        }

        $tag = Tag::findOrCreate($name, Article::tagType());
        $tagName = (string) $tag->name;

        if (! in_array($tagName, $this->form->tags, true)) {
            $this->form->tags[] = $tagName;
        }

        $this->tagSearch = '';
        unset($this->availableTags);

        Flux::toast(__('general.article_tag_created'));
    }

    public function save(): void
    {
        $this->authorizePermission('content-management.article.edit');

        $this->form->update();
        $this->tagSearch = '';
        unset($this->availableTags);
        $this->dispatch('panels.administrator.content-management.article.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.article_updated'));
    }
};
?>

<flux:modal name="content-management.article.edit" flyout position="right" class="md:max-w-2xl space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.edit') }} {{ __('general.article') }}</flux:heading>
        <flux:subheading>{{ __('general.content_management') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="form.title" label="{{ __('general.article_title') }}" icon="scroll-text" />

        <flux:pillbox wire:model="form.tags" variant="combobox" multiple label="{{ __('general.article_tags') }}">
            <x-slot name="input">
                <flux:pillbox.input wire:model="tagSearch" placeholder="{{ __('general.search_article_tags') }}" />
            </x-slot>

            @foreach ($this->availableTags as $tag)
                <flux:pillbox.option value="{{ $tag->name }}" :wire:key="$tag->id">{{ $tag->name }}</flux:pillbox.option>
            @endforeach

            <flux:pillbox.option.create wire:click="createTag" min-length="1">
                {{ __('general.create_article_tag') }} "<span wire:text="tagSearch"></span>"
            </flux:pillbox.option.create>
        </flux:pillbox>

        <flux:editor
            wire:model="form.content"
            label="{{ __('general.article_content') }}"
            toolbar="heading | bold italic strike | bullet ordered blockquote | link | align ~ undo redo"
            class="**:data-[slot=content]:min-h-[200px]!"
        />

        <flux:button type="submit" variant="primary" color="orange" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
