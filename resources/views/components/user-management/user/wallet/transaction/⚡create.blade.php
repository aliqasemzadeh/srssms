<?php

use App\Livewire\Forms\TransactionForm;
use App\Models\Finance\Wallet;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public User $user;

    public Wallet $wallet;

    public TransactionForm $form;

    public string $referenceSearch = '';

    public function mount(User $user, Wallet $wallet): void
    {
        abort_unless($wallet->user_id === $user->id, 404);

        $this->user = $user;
        $this->wallet = $wallet->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);
        $this->form->setWallet($this->wallet);
    }

    #[On('panels.administrator.user-management.user.wallet.transaction.create.assign-data')]
    public function assignData(): void
    {
        $this->wallet->refresh()->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);
        $this->form->setWallet($this->wallet);
        $this->referenceSearch = '';
        $this->resetValidation();
        unset($this->referenceOptions);

        Flux::modal('user-management.user.wallet.transaction.create')->show();
    }

    public function updated(string $property): void
    {
        if ($property === 'form.reference_type') {
            $this->form->reference_id = '';
            $this->referenceSearch = '';
            $this->resetValidation('form.reference_id');
            unset($this->referenceOptions);
        }

        if ($property === 'referenceSearch') {
            unset($this->referenceOptions);
        }
    }

    #[Computed]
    public function referenceOptions(): Collection
    {
        return $this->form->referenceOptions($this->referenceSearch)
            ->map(fn ($model) => (object) [
                'id' => $model->getKey(),
                'label' => $this->form->referenceOptionLabel($model),
            ]);
    }

    public function save(): void
    {
        $this->wallet->refresh()->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);
        $this->form->wallet = $this->wallet;

        $this->form->store();

        $this->wallet->refresh()->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);

        $this->form->setWallet($this->wallet);
        $this->referenceSearch = '';
        unset($this->referenceOptions);

        $this->dispatch('panels.administrator.user-management.user.wallet.transaction.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.transaction_created'));
    }
};
?>

@php
    $currency = $wallet->currency;
    $decimals = $form->decimals();
    $currencySymbol = $currency?->symbol ?? '';
@endphp

<flux:modal name="user-management.user.wallet.transaction.create" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.create') }} {{ __('general.transaction') }}</flux:heading>
        <flux:subheading>
            <span dir="ltr">{{ $currencySymbol }}</span> — {{ $user->full_name }}
        </flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:select wire:model="form.type" variant="listbox" searchable label="{{ __('general.type') }}">
            <flux:select.option value="credit">{{ __('general.transaction_type_credit') }}</flux:select.option>
            <flux:select.option value="debit">{{ __('general.transaction_type_debit') }}</flux:select.option>
        </flux:select>

        <flux:field>
            <flux:label>{{ __('general.amount') }}</flux:label>
            <flux:description>{{ __('general.amount_decimals_hint', ['decimals' => $decimals]) }}</flux:description>

            <x-finance.money-input
                wire:model="form.amount"
                :decimals="$decimals"
                :currency="$currency"
                :symbol="$currencySymbol"
            />

            <flux:error name="form.amount" />
        </flux:field>

        <flux:textarea
            wire:model="form.description"
            label="{{ __('general.description') }}"
            description="{{ __('general.transaction_description_hint') }}"
            placeholder="{{ __('general.description') }}..."
            rows="3"
        />

        <x-finance.transaction-reference-fields
            :reference-type="$form->reference_type"
            :reference-options="$this->referenceOptions"
        />

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
