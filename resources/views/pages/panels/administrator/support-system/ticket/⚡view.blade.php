<?php

use App\Enums\Support\TicketStatusEnum;
use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Livewire\Forms\Support\TicketReplyForm;
use App\Models\Support\Ticket;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use AuthorizesAdministratorPermissions;
    use WithFileUploads;

    public Ticket $ticket;

    public TicketReplyForm $form;

    public string $status = '';

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket->load(['user' => fn ($query) => $query->withTrashed()]);
        $this->status = $ticket->status->value;
        $this->form->setTicket($ticket);
    }

    #[Computed]
    public function replies(): Collection
    {
        return $this->ticket->replies()
            ->with(['user' => fn ($query) => $query->withTrashed()])
            ->get();
    }

    public function updateStatus(): void
    {
        $this->authorizePermission('support-system.ticket.update');

        $this->validate([
            'status' => ['required', 'string', Rule::enum(TicketStatusEnum::class)],
        ]);

        $this->ticket->update([
            'status' => $this->status,
        ]);

        $this->ticket->refresh();
        unset($this->replies);

        Flux::toast(__('general.ticket_status_updated'));
    }

    public function reply(): void
    {
        $this->authorizePermission('support-system.ticket.reply');

        if ($this->ticket->isClosed()) {
            Flux::toast(variant: 'danger', text: __('general.ticket_closed'));

            return;
        }

        $this->form->store();

        $this->ticket->refresh();
        $this->status = $this->ticket->status->value;
        unset($this->replies);

        Flux::toast(__('general.ticket_replied'));
    }
};
?>

<div>
    <x-slot name="title">{{ $ticket->title }} - {{ __('general.ticket') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.support_system') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.support-system.ticket.index') }}" wire:navigate>{{ __('general.tickets') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>#{{ $ticket->id }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

        <flux:card>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:heading size="lg">{{ $ticket->title }}</flux:heading>
                        <flux:badge size="sm" :color="$ticket->status->color()">{{ $ticket->status->label() }}</flux:badge>
                        <flux:badge size="sm" :color="$ticket->priority->color()">{{ $ticket->priority->label() }}</flux:badge>
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-zinc-500">
                        <span>{{ __('general.user') }}: {{ $ticket->user?->full_name ?? '—' }}</span>
                        <span>{{ __('general.created_at') }}: {{ $ticket->created_at->toDynamicFormat('Y/m/d H:i') }}</span>
                        <span>{{ __('general.last_replied_at') }}: {{ $ticket->last_replied_at?->toDynamicFormat('Y/m/d H:i') ?? '—' }}</span>
                    </div>
                </div>

                @can('support-system.ticket.update')
                    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-end lg:w-auto">
                        <flux:select wire:model="status" searchable label="{{ __('general.ticket_status') }}" class="min-w-48">
                            @foreach (TicketStatusEnum::cases() as $statusOption)
                                <flux:select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:button variant="primary" color="orange" icon="save" wire:click="updateStatus" class="w-full sm:w-auto">
                            {{ __('actions.save') }}
                        </flux:button>
                    </div>
                @endcan
            </div>
        </flux:card>

        @can('support-system.ticket.reply')
            @if (! $ticket->isClosed())
                <flux:card>
                    <flux:heading size="md" class="mb-4">{{ __('general.send_reply') }}</flux:heading>

                    <form wire:submit="reply" class="space-y-4">
                        <flux:editor
                            wire:model="form.body"
                            label="{{ __('general.ticket_message') }}"
                            toolbar="heading | bold italic strike | bullet ordered blockquote | link | align ~ undo redo"
                            class="**:data-[slot=content]:min-h-[160px]!"
                        />

                        <flux:file-upload wire:model="form.file" label="{{ __('general.ticket_attachment') }}">
                            <flux:file-upload.dropzone
                                heading="{{ __('general.ticket_attachment') }}"
                                text="PDF, JPG, PNG, ZIP, DOC"
                            />
                        </flux:file-upload>

                        @if ($form->file)
                            <flux:badge color="zinc" size="sm" icon="paperclip">{{ $form->file->getClientOriginalName() }}</flux:badge>
                        @endif

                        <flux:button type="submit" variant="primary" color="teal" icon="send" class="w-full">
                            {{ __('general.send_reply') }}
                        </flux:button>
                    </form>
                </flux:card>
            @else
            <flux:callout icon="circle-alert" variant="secondary">
                {{ __('general.ticket_closed') }}
            </flux:callout>
            @endif
        @endcan

        <div class="space-y-4">
            <flux:heading size="md">{{ __('general.ticket_replies') }}</flux:heading>

            @forelse ($this->replies as $reply)
                <flux:card wire:key="reply-{{ $reply->id }}" class="{{ $reply->user_id === $ticket->user_id ? 'border-s-4 border-s-amber-400' : 'border-s-4 border-s-indigo-400' }}">
                    <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <div class="font-medium">{{ $reply->user?->full_name ?? '—' }}</div>
                            <div class="text-xs text-zinc-500">
                                {{ $reply->created_at->toDynamicFormat('Y/m/d H:i:s') }}
                                @if ($reply->ip_address)
                                    · <span dir="ltr">{{ __('general.ip_address') }}: {{ $reply->ip_address }}</span>
                                @endif
                            </div>
                        </div>
                        <flux:badge size="sm" :color="$reply->user_id === $ticket->user_id ? 'amber' : 'indigo'">
                            {{ $reply->user_id === $ticket->user_id ? __('general.ticket_statuses.customer_reply') : __('general.ticket_statuses.support_reply') }}
                        </flux:badge>
                    </div>

                    <div class="prose prose-sm dark:prose-invert max-w-none">
                        {!! $reply->body !!}
                    </div>

                    @if ($reply->hasFile())
                        <div class="mt-3">
                            <flux:button size="sm" variant="subtle" icon="paperclip" :href="$reply->fileUrl()" target="_blank">
                                {{ $reply->file_name ?? __('general.download_attachment') }}
                            </flux:button>
                        </div>
                    @endif
                </flux:card>
            @empty
                <flux:card>
                    <div class="text-center text-zinc-500">{{ __('general.no_replies_found') }}</div>
                </flux:card>
            @endforelse
        </div>
    </div>
</div>
