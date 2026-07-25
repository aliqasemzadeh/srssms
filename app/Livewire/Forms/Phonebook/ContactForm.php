<?php

namespace App\Livewire\Forms\Phonebook;

use App\Enums\Phonebook\ContactGenderEnum;
use App\Enums\Phonebook\ContactPersonTypeEnum;
use App\Models\Phonebook\Contact;
use App\Models\Phonebook\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ContactForm extends Form
{
    public ?Contact $contact = null;

    public string $first_name = '';

    public string $last_name = '';

    public string $mobile = '';

    public string $company = '';

    public string $gender = '';

    public ?string $birth_date = null;

    public ?string $marriage_date = null;

    public string $address = '';

    public string $postal_code = '';

    public string $national_code = '';

    public string $economic_code = '';

    public string $person_type = '';

    /** @var array<int, int|string> */
    public array $group_ids = [];

    /** @var array<int, string> */
    public array $tags = [];

    public function setModel(Contact $contact): void
    {
        $contact->loadMissing(['groups', 'tags']);

        $this->contact = $contact;
        $this->first_name = $contact->first_name;
        $this->last_name = (string) ($contact->last_name ?? '');
        $this->mobile = $contact->mobile;
        $this->company = (string) ($contact->company ?? '');
        $this->gender = $contact->gender?->value ?? '';
        $this->birth_date = $contact->birth_date?->format('Y-m-d');
        $this->marriage_date = $contact->marriage_date?->format('Y-m-d');
        $this->address = (string) ($contact->address ?? '');
        $this->postal_code = (string) ($contact->postal_code ?? '');
        $this->national_code = (string) ($contact->national_code ?? '');
        $this->economic_code = (string) ($contact->economic_code ?? '');
        $this->person_type = $contact->person_type?->value ?? '';
        $this->group_ids = $contact->groups->pluck('id')->all();
        $this->tags = $contact->tags->pluck('name')->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = Auth::id();

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'mobile' => [
                'required',
                'string',
                'max:20',
                Rule::unique('phonebook_contacts', 'mobile')
                    ->where(fn ($query) => $query->where('user_id', $userId)->whereNull('deleted_at'))
                    ->ignore($this->contact?->id),
            ],
            'company' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', Rule::enum(ContactGenderEnum::class)],
            'birth_date' => ['nullable', 'date'],
            'marriage_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:2000'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'national_code' => ['nullable', 'string', 'max:20'],
            'economic_code' => ['nullable', 'string', 'max:50'],
            'person_type' => ['nullable', 'string', Rule::enum(ContactPersonTypeEnum::class)],
            'group_ids' => ['nullable', 'array'],
            'group_ids.*' => [
                'integer',
                Rule::exists('phonebook_groups', 'id')->where(fn ($query) => $query->where('user_id', $userId)->whereNull('deleted_at')),
            ],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
        ];
    }

    public function store(): Contact
    {
        $this->validate();

        $contact = Contact::query()->create($this->payload());

        $this->syncRelations($contact);

        return $contact;
    }

    public function update(): void
    {
        $this->validate();

        if (! $this->contact || $this->contact->user_id !== Auth::id()) {
            return;
        }

        $this->contact->update($this->payload());
        $this->syncRelations($this->contact);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(): array
    {
        return [
            'user_id' => Auth::id(),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name !== '' ? $this->last_name : null,
            'mobile' => $this->mobile,
            'company' => $this->company !== '' ? $this->company : null,
            'gender' => $this->gender !== '' ? $this->gender : null,
            'birth_date' => $this->birth_date ?: null,
            'marriage_date' => $this->marriage_date ?: null,
            'address' => $this->address !== '' ? $this->address : null,
            'postal_code' => $this->postal_code !== '' ? $this->postal_code : null,
            'national_code' => $this->national_code !== '' ? $this->national_code : null,
            'economic_code' => $this->economic_code !== '' ? $this->economic_code : null,
            'person_type' => $this->person_type !== '' ? $this->person_type : null,
        ];
    }

    protected function syncRelations(Contact $contact): void
    {
        $groupIds = collect($this->group_ids)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $ownedGroupIds = Group::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $groupIds)
            ->pluck('id')
            ->all();

        $contact->groups()->sync($ownedGroupIds);

        $tagNames = collect($this->tags)->filter()->map(fn ($tag) => (string) $tag)->values()->all();
        $contact->syncTagsWithType($tagNames, Contact::tagTypeFor(Auth::user()));
    }
}
