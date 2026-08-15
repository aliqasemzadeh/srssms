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
            [
                'first_name' => 'علی',
                'mobile' => '09121234567',
            ],
            [
                'first_name' => 'سارا',
                'mobile' => '09129876543',
            ],
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
