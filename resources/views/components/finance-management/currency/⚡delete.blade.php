<?php

use App\Models\Finance\Currency;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?Currency $currency = null;

    #[On('panels.administrator.finance-management.currency.delete.assign-data')]
    public function assignData(int $currency): void
    {
        $this->currency = Currency::findOrFail($currency);

        Flux::modal('finance-management.currency.delete')->show();
    }

    public function delete(): void
    {
        if (! $this->currency) {
            return;
        }

        $this->currency->delete();

        $this->currency = null;

        $this->dispatch('panels.administrator.finance-management.currency.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.currency_deleted'));
    }
};
?>

<flux:modal name="finance-management.currency.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>

        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($currency)
        <flux:callout icon="circle-dollar-sign" variant="secondary" inline>
            <flux:callout.heading>
                <span dir="ltr">{{ $currency->symbol }}</span> — {{ $currency->name }}
            </flux:callout.heading>
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
