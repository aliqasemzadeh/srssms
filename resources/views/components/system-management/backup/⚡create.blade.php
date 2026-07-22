<?php

use App\Jobs\RunBackupJob;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    #[Validate('required|in:both,database,files')]
    public string $type = 'both';

    #[Validate('required|in:local,remote')]
    public string $destination = 'local';

    #[On('panels.administrator.system-management.backup.create.assign-data')]
    public function assignData(): void
    {
        $this->reset(['type', 'destination']);

        Flux::modal('backup-create-modal')->show();
    }

    public function save(): void
    {
        $this->validate();

        RunBackupJob::dispatch($this->type, $this->destination);

        $this->dispatch('panels.administrator.system-management.backup.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.backup_queued'));
    }
};
?>

<flux:modal name="backup-create-modal" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.create') }} {{ __('general.backup') }}</flux:heading>
        <flux:subheading>{{ __('general.backup_create_hint') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:radio.group wire:model="type" label="{{ __('general.backup_type') }}" variant="cards" class="flex-col">
            <flux:radio value="both" icon="archive" label="{{ __('general.backup_type_both') }}" description="{{ __('general.backup_type_both_hint') }}" />
            <flux:radio value="database" icon="database" label="{{ __('general.backup_type_database') }}" description="{{ __('general.backup_type_database_hint') }}" />
            <flux:radio value="files" icon="folder-open" label="{{ __('general.backup_type_files') }}" description="{{ __('general.backup_type_files_hint') }}" />
        </flux:radio.group>

        <flux:radio.group wire:model="destination" label="{{ __('general.backup_destination') }}" variant="cards" class="flex-col">
            <flux:radio value="local" icon="hard-drive" label="{{ __('general.backup_destination_local') }}" description="{{ __('general.backup_destination_local_hint') }}" />
            <flux:radio value="remote" icon="server" label="{{ __('general.backup_destination_remote') }}" description="{{ __('general.backup_destination_remote_hint') }}" />
        </flux:radio.group>

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
