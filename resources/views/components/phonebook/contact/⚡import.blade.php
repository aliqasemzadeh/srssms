<?php

use App\Imports\Phonebook\ContactsImport;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $file = null;

    #[On('panels.user.phonebook.contact.import.assign-data')]
    public function assignData(): void
    {
        $this->reset('file');
        $this->resetValidation();
        Flux::modal('phonebook.contact.import')->show();
    }

    public function save(): void
    {
        $this->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
        ]);

        $import = new ContactsImport(Auth::user());
        Excel::import($import, $this->file);

        $this->reset('file');
        $this->dispatch('panels.user.phonebook.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.contacts_imported', [
            'imported' => $import->imported,
            'skipped' => $import->skipped,
        ]));
    }
};
?>

<flux:modal name="phonebook.contact.import" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.import') }} {{ __('general.contacts') }}</flux:heading>
        <flux:subheading>{{ __('general.import_contacts_hint') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:field>
            <flux:label>{{ __('general.file') }}</flux:label>
            <flux:file-upload wire:model="file">
                <flux:file-upload.dropzone inline heading="{{ __('general.upload_file_hint') }}" text="Excel / CSV" />
            </flux:file-upload>
            <flux:error name="file" />
        </flux:field>

        <flux:button type="submit" variant="primary" color="teal" class="w-full" icon="upload">{{ __('actions.import') }}</flux:button>
    </form>
</flux:modal>
