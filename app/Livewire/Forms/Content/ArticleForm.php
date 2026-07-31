<?php

namespace App\Livewire\Forms\Content;

use App\Models\Content\Article;
use Illuminate\Support\Facades\Auth;
use Livewire\Form;

class ArticleForm extends Form
{
    public ?Article $article = null;

    public string $title = '';

    public string $content = '';

    /** @var array<int, string> */
    public array $tags = [];

    public function setModel(Article $article): void
    {
        $article->loadMissing('tags');

        $this->article = $article;
        $this->title = $article->title;
        $this->content = $article->content;
        $this->tags = $article->tags
            ->map(fn ($tag) => (string) $tag->name)
            ->values()
            ->all();
    }

    public function resetForCreate(): void
    {
        $this->reset();
        $this->article = null;
        $this->title = '';
        $this->content = '';
        $this->tags = [];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => [
                'required',
                'string',
                'max:500000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (trim(strip_tags((string) $value)) === '') {
                        $fail(__('validation.required', ['attribute' => __('general.article_content')]));
                    }
                },
            ],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'title' => __('general.article_title'),
            'content' => __('general.article_content'),
            'tags' => __('general.article_tags'),
        ];
    }

    public function store(): Article
    {
        $this->validate();

        $article = Article::query()->create([
            'title' => $this->title,
            'content' => $this->content,
            'user_id' => Auth::id(),
        ]);

        $this->syncTags($article);

        return $article;
    }

    public function update(): void
    {
        $this->validate();

        if (! $this->article) {
            return;
        }

        $this->article->update([
            'title' => $this->title,
            'content' => $this->content,
        ]);

        $this->syncTags($this->article);
    }

    protected function syncTags(Article $article): void
    {
        $tagNames = collect($this->tags)
            ->filter()
            ->map(fn ($tag) => (string) $tag)
            ->values()
            ->all();

        $article->syncTagsWithType($tagNames, Article::tagType());
    }
}
