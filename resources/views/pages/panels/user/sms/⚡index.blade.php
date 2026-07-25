<?php

use App\Models\Sms\Message;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function messages(): LengthAwarePaginator
    {
        return Message::query()
            ->where('user_id', Auth::id())
            ->with('gateway')
            ->withCount('recipients')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('body', 'like', "%{$this->search}%")
                        ->orWhere('number', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(config('general.per_page', 10));
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.sms_messages') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.sms_messages') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:button variant="primary" color="teal" icon="send" :href="route('panels.user.sms.send')" wire:navigate>
                {{ __('general.send_sms') }}
            </flux:button>
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable />
            </div>

            <flux:table :paginate="$this->messages">
                <flux:table.columns>
                    <flux:table.column>{{ __('general.message_body') }}</flux:table.column>
                    <flux:table.column>{{ __('general.sms_gateway') }}</flux:table.column>
                    <flux:table.column>{{ __('general.recipients') }}</flux:table.column>
                    <flux:table.column>{{ __('general.parts_count') }}</flux:table.column>
                    <flux:table.column>{{ __('general.cost') }}</flux:table.column>
                    <flux:table.column>{{ __('general.status') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->messages as $message)
                        <flux:table.row :key="$message->id">
                            <flux:table.cell>{{ \Illuminate\Support\Str::limit($message->body, 50) }}</flux:table.cell>
                            <flux:table.cell>{{ $message->gateway?->title ?: '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="sky">{{ $message->recipients_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $message->parts_count }}</flux:table.cell>
                            <flux:table.cell>
                                {{ $message->cost !== null ? number_format($message->cost).' '.__('general.rial') : '—' }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $message->status->color() }}">{{ $message->status->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $message->created_at->toDynamicFormat('Y/m/d H:i') }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</div>
