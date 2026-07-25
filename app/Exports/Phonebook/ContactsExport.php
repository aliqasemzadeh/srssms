<?php

namespace App\Exports\Phonebook;

use App\Models\Phonebook\Contact;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ContactsExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected User $user,
        protected ?int $groupId = null,
    ) {}

    public function collection(): Collection
    {
        return Contact::query()
            ->ownedBy($this->user)
            ->with(['groups', 'tags'])
            ->when($this->groupId, fn ($query) => $query->whereHas('groups', fn ($q) => $q->where('phonebook_groups.id', $this->groupId)))
            ->orderBy('first_name')
            ->get()
            ->map(fn (Contact $contact) => [
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'mobile' => $contact->mobile,
                'company' => $contact->company,
                'gender' => $contact->gender?->value,
                'birth_date' => $contact->birth_date?->format('Y-m-d'),
                'marriage_date' => $contact->marriage_date?->format('Y-m-d'),
                'address' => $contact->address,
                'postal_code' => $contact->postal_code,
                'national_code' => $contact->national_code,
                'economic_code' => $contact->economic_code,
                'person_type' => $contact->person_type?->value,
                'groups' => $contact->groups->pluck('name')->implode(', '),
                'tags' => $contact->tags->pluck('name')->implode(', '),
            ]);
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'first_name',
            'last_name',
            'mobile',
            'company',
            'gender',
            'birth_date',
            'marriage_date',
            'address',
            'postal_code',
            'national_code',
            'economic_code',
            'person_type',
            'groups',
            'tags',
        ];
    }
}
