<?php

use App\Livewire\Forms\WalletForm;
use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public User $user;

    public WalletForm $form;

    public string $currencySearch = '';

    public function mount(User $user): void
    {
        $this->form->setUser($user);
    }

    #[On('panels.administrator.user-management.user.wallet.create.assign-data')]
    public function assignData(?int $currencyId = null): void
    {
        $this->form->setUser($this->user);
        $this->currencySearch = '';
        $this->resetValidation();

        if ($currencyId) {
            $this->form->currency_id = (string) $currencyId;
        }

        Flux::modal('user-management.user.wallet.create')->show();
    }

    #[Computed]
    public function currencies(): Collection
    {
        $currencies = $this->form->availableCurrencies();

        if (blank($this->currencySearch)) {
            return $currencies;
        }

        $search = mb_strtolower($this->currencySearch);

        return $currencies
            ->filter(function ($currency) use ($search) {
                return str_contains(mb_strtolower($currency->name), $search)
                    || str_contains(mb_strtolower($currency->symbol), $search);
            })
            ->values();
    }

    public function save(): void
    {
        $this->form->setUser($this->user);
        $this->form->store();

        $this->form->reset('currency_id');
        $this->currencySearch = '';

        $this->dispatch('panels.administrator.user-management.user.wallet.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.wallet_created'));
    }
};
?>

<flux:modal name="user-management.user.wallet.create" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.create') }} {{ __('general.wallet') }}</flux:heading>
        <flux:subheading>{{ $user->full_name }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:select wire:model="form.currency_id" variant="combobox" :filter="false" label="{{ __('general.currency') }}" placeholder="{{ __('general.currency') }}...">
            <x-slot name="input">
                <flux:select.input wire:model.live.debounce.300ms="currencySearch" placeholder="{{ __('general.currency') }}..." />
            </x-slot>

            @forelse ($this->currencies as $currency)
                <flux:select.option value="{{ $currency->id }}" wire:key="wallet-create-currency-{{ $currency->id }}">
                    <span dir="ltr">{{ $currency->symbol }}</span> — {{ $currency->name }}
                </flux:select.option>
            @empty
                <flux:select.option value="" disabled>{{ __('general.no_results_found') }}</flux:select.option>
            @endforelse
        </flux:select>

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
