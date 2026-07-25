<?php

namespace App\Imports\Phonebook;

use App\Enums\Phonebook\ContactGenderEnum;
use App\Enums\Phonebook\ContactPersonTypeEnum;
use App\Models\Phonebook\Contact;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ContactsImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    public int $skipped = 0;

    public function __construct(
        protected User $user,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $firstName = trim((string) ($row['first_name'] ?? $row['name'] ?? ''));
            $mobile = trim((string) ($row['mobile'] ?? $row['phone'] ?? ''));

            if ($firstName === '' || $mobile === '') {
                $this->skipped++;

                continue;
            }

            $exists = Contact::query()
                ->ownedBy($this->user)
                ->where('mobile', $mobile)
                ->exists();

            if ($exists) {
                $this->skipped++;

                continue;
            }

            $gender = $this->normalizeEnum((string) ($row['gender'] ?? ''), ContactGenderEnum::class);
            $personType = $this->normalizeEnum((string) ($row['person_type'] ?? ''), ContactPersonTypeEnum::class);

            Contact::query()->create([
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
