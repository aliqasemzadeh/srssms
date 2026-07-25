<?php

namespace App\Livewire\Forms\Phonebook;

use App\Enums\Phonebook\ContactNoteStatusEnum;
use App\Models\Phonebook\Contact;
use App\Models\Phonebook\Note;
use App\Support\JalaliDate;
use Carbon\Carbon;
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
        $this->remind_at = JalaliDate::format($note->remind_at, app()->getLocale() === 'fa' ? 'Y/m/d' : 'Y-m-d');
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
            'remind_at' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (blank($value)) {
                        return;
                    }

                    $date = $this->parseRemindAt($value);

                    if (! $date) {
                        $fail(__('validation.date', ['attribute' => __('general.remind_at')]));

                        return;
                    }

                    if ($date->startOfDay()->lte(now()->startOfDay())) {
                        $fail(__('validation.after', [
                            'attribute' => __('general.remind_at'),
                            'date' => __('validation.today') !== 'validation.today'
                                ? __('validation.today')
                                : (app()->getLocale() === 'fa' ? 'امروز' : 'today'),
                        ]));
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'contact_id' => __('general.contact'),
            'body' => __('general.note'),
            'status' => __('general.note_status'),
            'remind_at' => __('general.remind_at'),
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
            'remind_at' => $this->gregorianRemindAt(),
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

        $remindAt = $this->gregorianRemindAt();

        $this->note->update([
            'contact_id' => $this->contact_id,
            'body' => $this->body,
            'status' => $this->status !== '' ? $this->status : null,
            'remind_at' => $remindAt,
            'reminded_at' => $remindAt ? $this->note->reminded_at : null,
        ]);
    }

    protected function gregorianRemindAt(): ?string
    {
        return JalaliDate::toGregorianString($this->remind_at);
    }

    protected function parseRemindAt(mixed $value): ?Carbon
    {
        return JalaliDate::parse(is_string($value) || is_numeric($value) ? (string) $value : null);
    }
}
