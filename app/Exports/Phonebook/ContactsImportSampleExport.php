<?php

namespace App\Exports\Phonebook;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ContactsImportSampleExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return collect([
            ['علی', '09121234567'],
            ['سارا', '09129876543'],
        ]);
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'first_name',
            'mobile',
        ];
    }
}
