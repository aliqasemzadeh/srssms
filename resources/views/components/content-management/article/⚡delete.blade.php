<?php

use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Models\Content\Article;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use AuthorizesAdministratorPermissions;

    public ?Article $article = null;

    #[On('panels.administrator.content-management.article.delete.assign-data')]
    public function assignData(int $article): void
    {
        $this->authorizePermission('content-management.article.delete');

        $this->article = Article::query()->findOrFail($article);

        Flux::modal('content-management.article.delete')->show();
    }

    public function delete(): void
    {
        $this->authorizePermission('content-management.article.delete');

        if (! $this->article) {
            return;
        }

        $this->article->delete();
        $this->article = null;
        $this->dispatch('panels.administrator.content-management.article.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.article_deleted'));
    }
};
?>

<flux:modal name="content-management.article.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>
        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($article)
        <flux:callout icon="scroll-text" variant="secondary" inline>
            <flux:callout.heading>{{ $article->title }}</flux:callout.heading>
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
