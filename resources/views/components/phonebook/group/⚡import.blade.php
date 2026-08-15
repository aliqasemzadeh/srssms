<?php

use App\Exports\Phonebook\ContactsImportSampleExport;
use App\Imports\Phonebook\ContactsImport;
use App\Models\Phonebook\Group;
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

    public ?Group $group = null;

    public ?TemporaryUploadedFile $file = null;

    public string $step = 'upload';

    public int $previewTotal = 0;

    public int $previewValid = 0;

    public int $previewInvalid = 0;

    public int $previewDuplicate = 0;

    #[On('panels.user.phonebook.group.import.assign-data')]
    public function assignData(int $group): void
    {
        $this->group = Group::query()
            ->where('user_id', Auth::id())
            ->findOrFail($group);

        $this->resetPreview();
        $this->resetValidation();

        Flux::modal('phonebook.group.import')->show();
    }

    public function updatedFile(): void
    {
        $this->step = 'upload';
        $this->previewTotal = 0;
        $this->previewValid = 0;
        $this->previewInvalid = 0;
        $this->previewDuplicate = 0;
        $this->resetValidation();
    }

    public function downloadSample()
    {
        return Excel::download(
            new ContactsImportSampleExport,
            'contacts-import-sample.xlsx'
        );
    }

    public function preview(): void
    {
        $this->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
        ]);

        if (! $this->group || $this->group->user_id !== Auth::id()) {
            return;
        }

        $import = new ContactsImport(Auth::user(), $this->group, dryRun: true);
        Excel::import($import, $this->file);

        $this->previewTotal = $import->total;
        $this->previewValid = $import->valid;
        $this->previewInvalid = $import->invalid;
        $this->previewDuplicate = $import->duplicate;
        $this->step = 'preview';
    }

    public function confirmImport(): void
    {
        $this->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
        ]);

        if (! $this->group || $this->group->user_id !== Auth::id()) {
            return;
        }

        if ($this->previewValid < 1) {
            Flux::toast(__('general.import_no_valid_contacts'));

            return;
        }

        $import = new ContactsImport(Auth::user(), $this->group, dryRun: false);
        Excel::import($import, $this->file);

        $this->resetPreview();
        $this->dispatch('panels.user.phonebook.group.view.refresh');
        $this->dispatch('panels.user.phonebook.group.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.contacts_imported', [
            'imported' => $import->imported,
            'skipped' => $import->skipped,
        ]));
    }

    public function backToUpload(): void
    {
        $this->step = 'upload';
    }

    protected function resetPreview(): void
    {
        $this->reset('file');
        $this->step = 'upload';
        $this->previewTotal = 0;
        $this->previewValid = 0;
        $this->previewInvalid = 0;
        $this->previewDuplicate = 0;
    }
};
?>

<flux:modal name="phonebook.group.import" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.import') }} {{ __('general.contacts') }}</flux:heading>
        <flux:subheading>{{ $group?->name }}</flux:subheading>
    </div>

    <flux:text>{{ __('general.import_contacts_hint') }}</flux:text>

    <flux:button
        type="button"
        variant="primary"
        color="cyan"
        class="w-full"
        icon="download"
        wire:click="downloadSample"
    >
        {{ __('general.download_sample_file') }}
    </flux:button>

    @if ($step === 'upload')
        <form wire:submit="preview" class="space-y-6">
            <flux:field>
                <flux:label>{{ __('general.file') }}</flux:label>
                <flux:file-upload wire:model="file">
                    <flux:file-upload.dropzone inline heading="{{ __('general.upload_file_hint') }}" text="Excel / CSV" />
                </flux:file-upload>
                <flux:error name="file" />
            </flux:field>

            <flux:button type="submit" variant="primary" color="teal" class="w-full" icon="search">
                {{ __('general.preview_import') }}
            </flux:button>
        </form>
    @else
        <flux:callout icon="file-check" variant="secondary">
            <flux:callout.heading>{{ __('general.import_preview_summary', [
                'valid' => $previewValid,
                'invalid' => $previewInvalid,
                'duplicate' => $previewDuplicate,
                'total' => $previewTotal,
            ]) }}</flux:callout.heading>
        </flux:callout>

        <div class="flex flex-col gap-3">
            <flux:button
                type="button"
                variant="primary"
                color="teal"
                class="w-full"
                icon="upload"
                wire:click="confirmImport"
                :disabled="$previewValid < 1"
            >
                {{ __('general.confirm_import') }}
            </flux:button>

            <flux:button type="button" variant="ghost" class="w-full" wire:click="backToUpload">
                {{ __('general.back') }}
            </flux:button>
        </div>
    @endif
</flux:modal>
