<?php

namespace App\Imports\Phonebook;

use App\Enums\Phonebook\ContactGenderEnum;
use App\Enums\Phonebook\ContactPersonTypeEnum;
use App\Models\Phonebook\Contact;
use App\Models\Phonebook\Group;
use App\Models\User;
use App\Support\MobileNumber;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ContactsImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    public int $skipped = 0;

    public int $valid = 0;

    public int $invalid = 0;

    public int $duplicate = 0;

    public int $total = 0;

    public function __construct(
        protected User $user,
        protected ?Group $group = null,
        protected bool $dryRun = false,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $this->total++;

            $firstName = trim((string) ($row['first_name'] ?? $row['name'] ?? $row['username'] ?? ''));
            $mobileRaw = trim((string) ($row['mobile'] ?? $row['phone'] ?? ''));
            $mobile = $mobileRaw !== '' ? MobileNumber::normalize($mobileRaw) : '';

            if ($firstName === '' || $mobile === '' || ! MobileNumber::isValid($mobile)) {
                $this->invalid++;
                $this->skipped++;

                continue;
            }

            $this->valid++;

            $existing = Contact::query()
                ->ownedBy($this->user)
                ->where('mobile', $mobile)
                ->first();

            if ($existing) {
                $this->duplicate++;

                if ($this->dryRun) {
                    continue;
                }

                if ($this->group && ! $this->group->contacts()->where('phonebook_contacts.id', $existing->id)->exists()) {
                    $this->group->contacts()->attach($existing->id);
                    $this->imported++;
                } else {
                    $this->skipped++;
                }

                continue;
            }

            if ($this->dryRun) {
                continue;
            }

            $gender = $this->normalizeEnum((string) ($row['gender'] ?? ''), ContactGenderEnum::class);
            $personType = $this->normalizeEnum((string) ($row['person_type'] ?? ''), ContactPersonTypeEnum::class);

            $contact = Contact::query()->create([
                'user_id' => $this->user->id,
                'first_name' => $firstName,
                'last_name' => $this->nullableString($row['last_name'] ?? null),
                'mobile' => $mobile,
                'company' => $this->nullableString($row['company'] ?? null),
                'gender' => $gender,
                'birth_date' => $this->nullableString($row['birth_date'] ?? null),
                'marriage_date' => $this->nullableString($row['marriage_date'] ?? null),
                'address' => $this->nullableString($row['address'] ?? null),
                'postal_code' => $this->nullableString($row['postal_code'] ?? null),
                'national_code' => $this->nullableString($row['national_code'] ?? null),
                'economic_code' => $this->nullableString($row['economic_code'] ?? null),
                'person_type' => $personType,
            ]);

            if ($this->group) {
                $this->group->contacts()->attach($contact->id);
            }

            $this->imported++;
        }
    }

    protected function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }

    /**
     * @param  class-string<\BackedEnum>  $enum
     */
    protected function normalizeEnum(string $value, string $enum): ?string
    {
        $value = strtolower(trim($value));

        if ($value === '') {
            return null;
        }

        return $enum::tryFrom($value)?->value;
    }
}
