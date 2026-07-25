<?php

use App\Enums\Support\TicketPriorityEnum;
use App\Livewire\Forms\Support\TicketForm;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public TicketForm $form;

    public function mount(): void
    {
        $this->form->priority = TicketPriorityEnum::Medium->value;
    }

    public function save(): void
    {
        $ticket = $this->form->store();

        Flux::toast(__('general.ticket_created'));

        $this->redirect(route('panels.user.ticket.view', $ticket), navigate: true);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.create_ticket') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item href="{{ route('panels.user.ticket.index') }}" wire:navigate>{{ __('general.my_tickets') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.create_ticket') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

        <flux:card class="max-w-3xl">
            <flux:heading size="lg" class="mb-6">{{ __('general.create_ticket') }}</flux:heading>

            <form wire:submit="save" class="space-y-5">
                <flux:input wire:model="form.title" label="{{ __('general.ticket_title') }}" />

                <flux:select wire:model="form.priority" searchable label="{{ __('general.ticket_priority') }}">
                    @foreach (TicketPriorityEnum::cases() as $priorityOption)
                        <flux:select.option value="{{ $priorityOption->value }}">{{ $priorityOption->label() }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:editor
                    wire:model="form.body"
                    label="{{ __('general.ticket_message') }}"
                    toolbar="heading | bold italic strike | bullet ordered blockquote | link | align ~ undo redo"
                    class="**:data-[slot=content]:min-h-[200px]!"
                />

                <flux:file-upload wire:model="form.file" label="{{ __('general.ticket_attachment') }}">
                    <flux:file-upload.dropzone
                        heading="{{ __('general.upload_file_hint') }}"
                        text="PDF, JPG, PNG, ZIP, DOC (max 5MB)"
                    />
                </flux:file-upload>

                @if ($form->file)
                    <flux:badge color="zinc" size="sm" icon="paperclip">{{ $form->file->getClientOriginalName() }}</flux:badge>
                @endif

                <flux:button type="submit" variant="primary" color="teal" icon="plus" class="w-full">
                    {{ __('actions.save') }}
                </flux:button>
            </form>
        </flux:card>
    </div>
</div>
