<?php

use App\Models\Finance\Withdrawal;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?Withdrawal $withdrawal = null;

    #[On('panels.administrator.finance-management.withdrawal.delete.assign-data')]
    public function assignData(int $withdrawal): void
    {
        $this->withdrawal = Withdrawal::query()
            ->with([
                'user' => fn ($query) => $query->withTrashed(),
                'wallet.currency' => fn ($query) => $query->withTrashed(),
            ])
            ->findOrFail($withdrawal);

        Flux::modal('finance-management.withdrawal.delete')->show();
    }

    public function delete(): void
    {
        if (! $this->withdrawal) {
            return;
        }

        $this->withdrawal->delete();

        $this->withdrawal = null;

        $this->dispatch('panels.administrator.finance-management.withdrawal.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.withdrawal_deleted'));
    }
};
?>

<flux:modal name="finance-management.withdrawal.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>

        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($withdrawal)
        @php
            $decimals = $withdrawal->wallet?->currency?->decimals ?? 8;
            $symbol = $withdrawal->wallet?->currency?->symbol ?? '';
            $userLabel = $withdrawal->user?->full_name ?? __('general.deleted');
        @endphp
        <flux:callout icon="arrow-up-from-line" variant="secondary" inline>
            <flux:callout.heading>
                {{ $userLabel }}
                —
                <span dir="ltr">{{ number_format((float) $withdrawal->amount, $decimals) }} {{ $symbol }}</span>
            </flux:callout.heading>
            <flux:callout.text>
                {{ $withdrawal->status->label() }}
                @if ($withdrawal->tracking_code)
                    — <span dir="ltr">{{ $withdrawal->tracking_code }}</span>
                @endif
            </flux:callout.text>
        </flux:callout>
    @endif

    <div class="flex gap-2">
        <flux:spacer />

        <flux:modal.close>
            <flux:button variant="ghost">{{ __('actions.cancel') }}</flux:button>
        </flux:modal.close>

        <flux:button wire:click="delete" variant="danger" icon="trash" icon:variant="outline">
            {{ __('actions.delete') }}
        </flux:button>
    </div>
</flux:modal>
