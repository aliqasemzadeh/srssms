<?php

use App\Models\Phonebook\Contact;
use App\Models\Phonebook\Note;
use App\Models\Sms\Message;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public Contact $contact;

    public function mount(Contact $contact): void
    {
        abort_unless($contact->user_id === Auth::id(), 404);

        $this->contact = $contact->load(['groups', 'tags']);
    }

    #[Computed]
    public function timeline(): Collection
    {
        $notes = Note::query()
            ->where('contact_id', $this->contact->id)
            ->ownedBy(Auth::user())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Note $note) => [
                'type' => 'note',
                'id' => 'note-'.$note->id,
                'at' => $note->created_at,
                'title' => __('general.note'),
                'body' => $note->body,
                'status' => $note->status,
                'remind_at' => $note->remind_at,
                'reminded_at' => $note->reminded_at,
                'note_id' => $note->id,
            ]);

        $messages = Message::query()
            ->where('user_id', Auth::id())
            ->whereHas('recipients', function ($query) {
                $query->where(function ($q) {
                    $q->where('contact_id', $this->contact->id)
                        ->orWhere('mobile', $this->contact->mobile);
                });
            })
            ->with(['recipients' => fn ($q) => $q->where(function ($q) {
                $q->where('contact_id', $this->contact->id)
                    ->orWhere('mobile', $this->contact->mobile);
            })])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (Message $message) => [
                'type' => 'sms',
                'id' => 'sms-'.$message->id,
                'at' => $message->sent_at ?? $message->created_at,
                'title' => __('general.sms_message'),
                'body' => $message->body,
                'status' => $message->status,
                'parts_count' => $message->parts_count,
                'cost' => $message->cost,
            ]);

        return $notes->concat($messages)
            ->sortByDesc(fn (array $item) => $item['at']?->timestamp ?? 0)
            ->values();
    }

    #[On('panels.user.phonebook.view.refresh')]
    public function refresh(): void
    {
        $this->contact->refresh()->load(['groups', 'tags']);
        unset($this->timeline);
    }
};
?>

<div>
    <x-slot name="title">{{ $contact->full_name }} - {{ __('general.phonebook') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item href="{{ route('panels.user.phonebook.index') }}" wire:navigate>{{ __('general.phonebook') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $contact->full_name }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <div class="flex flex-wrap gap-2">
                <flux:button size="sm" variant="primary" color="teal" icon="sticky-note" wire:click="$dispatch('panels.user.phonebook.note.create.assign-data', { contactId: {{ $contact->id }} })">
                    {{ __('actions.create') }} {{ __('general.note') }}
                </flux:button>
                <flux:button size="sm" variant="primary" color="sky" icon="send" :href="route('panels.user.sms.send', ['contacts' => $contact->id])" wire:navigate>
                    {{ __('general.send_sms') }}
                </flux:button>
                <flux:button size="sm" variant="primary" color="blue" icon="pencil" wire:click="$dispatch('panels.user.phonebook.contact.edit.assign-data', { contact: {{ $contact->id }} })">
                    {{ __('general.edit') }}
                </flux:button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <flux:card class="space-y-4 lg:col-span-1">
                <flux:heading size="lg">{{ $contact->full_name }}</flux:heading>
                <flux:text><span dir="ltr">{{ $contact->mobile }}</span></flux:text>

                <div class="space-y-2 text-sm">
                    <div><span class="text-zinc-500">{{ __('general.company') }}:</span> {{ $contact->company ?: '—' }}</div>
                    <div><span class="text-zinc-500">{{ __('general.gender') }}:</span> {{ $contact->gender?->label() ?: '—' }}</div>
                    <div><span class="text-zinc-500">{{ __('general.person_type') }}:</span> {{ $contact->person_type?->label() ?: '—' }}</div>
                    <div><span class="text-zinc-500">{{ __('general.birth_date') }}:</span> {{ $contact->birth_date?->toDynamicFormat('Y/m/d') ?: '—' }}</div>
                    <div><span class="text-zinc-500">{{ __('general.marriage_date') }}:</span> {{ $contact->marriage_date?->toDynamicFormat('Y/m/d') ?: '—' }}</div>
                    <div><span class="text-zinc-500">{{ __('general.national_code') }}:</span> {{ $contact->national_code ?: '—' }}</div>
                    <div><span class="text-zinc-500">{{ __('general.economic_code') }}:</span> {{ $contact->economic_code ?: '—' }}</div>
                    <div><span class="text-zinc-500">{{ __('general.postal_code') }}:</span> {{ $contact->postal_code ?: '—' }}</div>
                    <div><span class="text-zinc-500">{{ __('general.address') }}:</span> {{ $contact->address ?: '—' }}</div>
                </div>

                <div class="flex flex-wrap gap-1">
                    @foreach ($contact->groups as $group)
                        <flux:badge size="sm" color="teal">{{ $group->name }}</flux:badge>
                    @endforeach
                    @foreach ($contact->tags as $tag)
                        <flux:badge size="sm" color="violet">{{ $tag->name }}</flux:badge>
                    @endforeach
                </div>
            </flux:card>

            <flux:card class="space-y-4 lg:col-span-2">
                <flux:heading size="lg">{{ __('general.communications') }}</flux:heading>

                <flux:timeline>
                    @forelse ($this->timeline as $item)
                        <flux:timeline.item>
                            <flux:timeline.indicator color="{{ $item['type'] === 'sms' ? 'sky' : 'amber' }}">
                                @if ($item['type'] === 'sms')
                                    <flux:icon.message-square variant="micro" />
                                @else
                                    <flux:icon.sticky-note variant="micro" />
                                @endif
                            </flux:timeline.indicator>

                            <flux:timeline.content>
                                <flux:heading size="sm">
                                    {{ $item['title'] }}
                                    <flux:text inline class="text-xs text-zinc-500">· {{ $item['at']?->toDynamicFormat('Y/m/d H:i') }}</flux:text>
                                </flux:heading>
                                <flux:text class="mt-1">{{ $item['body'] }}</flux:text>

                                <div class="mt-2 flex flex-wrap gap-2">
                                    @if ($item['type'] === 'note' && $item['status'])
                                        <flux:badge size="sm" color="{{ $item['status']->color() }}">{{ $item['status']->label() }}</flux:badge>
                                    @endif
                                    @if ($item['type'] === 'note' && $item['remind_at'])
                                        <flux:badge size="sm" color="amber">{{ __('general.remind_at') }}: {{ $item['remind_at']->toDynamicFormat('Y/m/d') }}</flux:badge>
                                    @endif
                                    @if ($item['type'] === 'sms' && $item['status'])
                                        <flux:badge size="sm" color="{{ $item['status']->color() }}">{{ $item['status']->label() }}</flux:badge>
                                    @endif
                                    @if ($item['type'] === 'sms' && $item['cost'])
                                        <flux:badge size="sm" color="zinc">{{ number_format($item['cost']) }} {{ __('general.rial') }}</flux:badge>
                                    @endif
                                </div>
                            </flux:timeline.content>
                        </flux:timeline.item>
                    @empty
                        <flux:text>{{ __('general.no_communications') }}</flux:text>
                    @endforelse
                </flux:timeline>
            </flux:card>
        </div>
    </div>

    <livewire:phonebook.contact.edit :key="'phonebook-view-contact-edit'" />
    <livewire:phonebook.note.create :key="'phonebook-view-note-create'" />
    <livewire:phonebook.note.edit :key="'phonebook-view-note-edit'" />
    <livewire:phonebook.note.delete :key="'phonebook-view-note-delete'" />
</div>
