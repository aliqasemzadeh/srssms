<?php

use App\Livewire\Forms\CurrencyForm;
use App\Models\Finance\Currency;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public CurrencyForm $form;

    #[On('panels.administrator.finance-management.currency.edit.assign-data')]
    public function assignData(int $currency): void
    {
        $this->form->setModel(Currency::findOrFail($currency));
        $this->resetValidation();

        Flux::modal('finance-management.currency.edit')->show();
    }

    public function removeLogo(): void
    {
        $this->form->removeLogo();

        Flux::toast(__('general.logo_removed'));
    }

    public function save(): void
    {
        $this->form->update();

        $this->dispatch('panels.administrator.finance-management.currency.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.currency_updated'));
    }
};
?>

<flux:modal name="finance-management.currency.edit" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.edit') }} {{ __('general.currency') }}</flux:heading>
        <flux:subheading>{{ __('general.currencies') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="form.symbol" label="{{ __('general.symbol') }}" icon="circle-dollar-sign" placeholder="IRR, USD, BTC..." dir="ltr" />

        <flux:input wire:model="form.name" label="{{ __('general.name') }}" icon="banknote" placeholder="{{ __('general.name') }}..." />

        <flux:select wire:model="form.type" variant="listbox" searchable label="{{ __('general.type') }}">
            <flux:select.option value="fiat">{{ __('general.currency_type_fiat') }}</flux:select.option>
            <flux:select.option value="crypto">{{ __('general.currency_type_crypto') }}</flux:select.option>
            <flux:select.option value="commodity">{{ __('general.currency_type_commodity') }}</flux:select.option>
        </flux:select>

        <flux:input wire:model="form.decimals" type="number" min="0" max="18" label="{{ __('general.decimals') }}" />

        <flux:field>
            <flux:label>{{ __('general.logo') }}</flux:label>

            @if ($form->current_logo)
                <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <img src="{{ asset('storage/' . $form->current_logo) }}" alt="{{ __('general.logo') }}" class="h-12 w-auto rounded-md object-contain" />
                    <flux:tooltip content="{{ __('general.remove') }}">
                        <flux:button type="button" size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="removeLogo" wire:confirm="{{ __('general.are_you_sure') }}" />
                    </flux:tooltip>
                </div>
            @endif

            <flux:file-upload wire:model="form.logo">
                <flux:file-upload.dropzone inline heading="{{ __('general.upload_file_hint') }}" text="PNG, JPG, SVG (max 2MB)" />
            </flux:file-upload>
            <flux:error name="form.logo" />
        </flux:field>

        <div class="flex items-center justify-between gap-3">
            <flux:label>{{ __('general.is_active') }}</flux:label>
            <flux:switch wire:model="form.is_active" />
        </div>

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
