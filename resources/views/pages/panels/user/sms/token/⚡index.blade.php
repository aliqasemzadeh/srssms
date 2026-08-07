<?php

use App\Models\Sms\Token;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function tokens(): LengthAwarePaginator
    {
        return Token::query()
            ->where('user_id', Auth::id())
            ->withCount('logs')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('token', 'like', "%{$this->search}%");
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

    #[On('panels.user.sms.token.index.refresh')]
    public function refresh(): void
    {
        unset($this->tokens);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.sms_tokens') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.sms') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.sms_tokens') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <div class="sm:hidden shrink-0">
                <flux:dropdown align="end">
                    <flux:button icon:trailing="chevron-down">{{ __('general.actions') }}</flux:button>
                    <flux:menu>
                        <flux:menu.item icon="book-open" :href="route('panels.user.sms.token.doc')" wire:navigate>
                            {{ __('general.sms_api_docs') }}
                        </flux:menu.item>
                        <flux:menu.item icon="code" :href="route('panels.user.sms.token.sample')" wire:navigate>
                            {{ __('general.sms_api_samples') }}
                        </flux:menu.item>
                        <flux:menu.item icon="scroll-text" :href="route('panels.user.sms.token.logs')" wire:navigate>
                            {{ __('general.sms_token_logs') }}
                        </flux:menu.item>
                        <flux:menu.item icon="plus" wire:click="$dispatch('panels.user.sms.token.create.assign-data')">
                            {{ __('actions.create') }} {{ __('general.sms_token') }}
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>

            <div class="hidden items-center gap-2 sm:flex shrink-0">
                <flux:button variant="primary" color="zinc" icon="book-open" :href="route('panels.user.sms.token.doc')" wire:navigate>
                    {{ __('general.sms_api_docs') }}
                </flux:button>
                <flux:button variant="primary" color="indigo" icon="code" :href="route('panels.user.sms.token.sample')" wire:navigate>
                    {{ __('general.sms_api_samples') }}
                </flux:button>
                <flux:button variant="primary" color="sky" icon="scroll-text" :href="route('panels.user.sms.token.logs')" wire:navigate>
                    {{ __('general.sms_token_logs') }}
                </flux:button>
                <flux:button variant="primary" color="teal" icon="plus" wire:click="$dispatch('panels.user.sms.token.create.assign-data')">
                    {{ __('actions.create') }} {{ __('general.sms_token') }}
                </flux:button>
            </div>
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable class="min-w-0 flex-1 max-w-xs" />
            </div>

            <flux:table :paginate="$this->tokens">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('general.name') }}</flux:table.column>
                    <flux:table.column>{{ __('general.sms_token_value') }}</flux:table.column>
                    <flux:table.column>{{ __('general.allowed_ips') }}</flux:table.column>
                    <flux:table.column>{{ __('general.status') }}</flux:table.column>
                    <flux:table.column>{{ __('general.sms_token_logs') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'last_used_at'" :direction="$sortDirection" wire:click="sort('last_used_at')">{{ __('general.last_used_at') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->tokens as $token)
                        <flux:table.row :key="$token->id">
                            <flux:table.cell variant="strong">{{ $token->name }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:input
                                    :value="$token->token"
                                    size="sm"
                                    readonly
                                    copyable
                                    variant="filled"
                                    input:class="text-left font-mono text-xs"
                                    dir="ltr"
                                />
                            </flux:table.cell>
                            <flux:table.cell>
                                @php($ips = $token->allowed_ips ?? [])
                                @if (empty($ips))
                                    <flux:badge size="sm" color="zinc">{{ __('general.all_ips') }}</flux:badge>
                                @else
                                    <flux:badge size="sm" color="sky">{{ count($ips) }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $token->is_active ? 'green' : 'red' }}">
                                    {{ $token->is_active ? __('general.active') : __('general.inactive') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="violet">{{ $token->logs_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $token->last_used_at?->toDynamicFormat('Y/m/d H:i') ?: '—' }}</flux:table.cell>
                            <flux:table.cell>{{ $token->created_at->toDynamicFormat('Y/m/d H:i') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.user.sms.token.edit.assign-data', { token: {{ $token->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.user.sms.token.delete.assign-data', { token: {{ $token->id }} })" />
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:sms.token.create :key="'sms-token-create'" />
    <livewire:sms.token.edit :key="'sms-token-edit'" />
    <livewire:sms.token.delete :key="'sms-token-delete'" />
</div>
