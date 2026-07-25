<?php

namespace App\Livewire\Forms\Phonebook;

use App\Models\Phonebook\Contact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Form;
use Spatie\Tags\Tag;

class TagForm extends Form
{
    public ?Tag $tag = null;

    public string $name = '';

    public function setModel(Tag $tag): void
    {
        $this->tag = $tag;
        $this->name = $tag->name;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
        ];
    }

    public function store(): Tag
    {
        $this->validate();

        $type = Contact::tagTypeFor(Auth::user());

        return Tag::findOrCreate($this->name, $type);
    }

    public function update(): void
    {
        $this->validate();

        if (! $this->tag) {
            return;
        }

        $type = Contact::tagTypeFor(Auth::user());

        if ($this->tag->type !== $type) {
            return;
        }

        $this->tag->name = $this->name;
        $this->tag->save();
    }
}
