<?php

namespace App\Livewire\Forms\Phonebook;

use App\Enums\Phonebook\ContactNoteStatusEnum;
use App\Models\Phonebook\Contact;
use App\Models\Phonebook\Note;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

class NoteForm extends Form
{
    public ?Note $note = null;

    public ?int $contact_id = null;

    public string $body = '';

    public string $status = '';

    public ?string $remind_at = null;

    public function setModel(Note $note): void
    {
        $this->note = $note;
        $this->contact_id = $note->contact_id;
        $this->body = $note->body;
        $this->status = $note->status?->value ?? '';
        $this->remind_at = $note->remind_at?->format('Y-m-d');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = Auth::id();

        return [
            'contact_id' => [
                'required',
                'integer',
                Rule::exists('phonebook_contacts', 'id')->where(fn ($query) => $query->where('user_id', $userId)->whereNull('deleted_at')),
            ],
            'body' => ['required', 'string', 'max:5000'],
            'status' => ['nullable', 'string', Rule::enum(ContactNoteStatusEnum::class)],
            'remind_at' => ['nullable', 'date'],
        ];
    }

    public function store(): Note
    {
        if ($this->status === '') {
            $this->status = '';
        }

        $this->validate();

        Contact::query()->ownedBy(Auth::user())->findOrFail($this->contact_id);

        return Note::query()->create([
            'user_id' => Auth::id(),
            'contact_id' => $this->contact_id,
            'body' => $this->body,
            'status' => $this->status !== '' ? $this->status : null,
            'remind_at' => $this->remind_at ?: null,
            'reminded_at' => null,
        ]);
    }

    public function update(): void
    {
        $this->validate();

        if (! $this->note || $this->note->user_id !== Auth::id()) {
            return;
        }

        Contact::query()->ownedBy(Auth::user())->findOrFail($this->contact_id);

        $this->note->update([
            'contact_id' => $this->contact_id,
            'body' => $this->body,
            'status' => $this->status !== '' ? $this->status : null,
            'remind_at' => $this->remind_at ?: null,
            'reminded_at' => $this->remind_at ? $this->note->reminded_at : null,
        ]);
    }
}
