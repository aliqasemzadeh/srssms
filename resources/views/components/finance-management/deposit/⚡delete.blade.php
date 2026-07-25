<?php

use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Models\Finance\Deposit;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use AuthorizesAdministratorPermissions;

    public ?Deposit $deposit = null;

    #[On('panels.administrator.finance-management.deposit.delete.assign-data')]
    public function assignData(int $deposit): void
    {
        $this->authorizePermission('finance-management.deposit.delete');

        $this->deposit = Deposit::query()
            ->with([
                'user' => fn ($query) => $query->withTrashed(),
                'wallet.currency' => fn ($query) => $query->withTrashed(),
            ])
            ->findOrFail($deposit);

        Flux::modal('finance-management.deposit.delete')->show();
    }

    public function delete(): void
    {
        $this->authorizePermission('finance-management.deposit.delete');

        if (! $this->deposit) {
            return;
        }

        $this->deposit->delete();

        $this->deposit = null;

        $this->dispatch('panels.administrator.finance-management.deposit.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.deposit_deleted'));
    }
};
?>

<flux:modal name="finance-management.deposit.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>

        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($deposit)
        @php
            $decimals = $deposit->wallet?->currency?->decimals ?? 8;
            $symbol = $deposit->wallet?->currency?->symbol ?? '';
            $userLabel = $deposit->user?->full_name ?? __('general.deleted');
        @endphp
        <flux:callout icon="arrow-down-to-line" variant="secondary" inline>
            <flux:callout.heading>
                {{ $userLabel }}
                —
                <span dir="ltr">{{ number_format((float) $deposit->amount, $decimals) }} {{ $symbol }}</span>
            </flux:callout.heading>
            <flux:callout.text>
                {{ $deposit->status->label() }}
                @if ($deposit->tracking_code)
                    — <span dir="ltr">{{ $deposit->tracking_code }}</span>
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
