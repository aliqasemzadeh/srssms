<?php

use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Enums\UserAccountTypeEnum;
use App\Livewire\Forms\UserAccountForm;
use App\Models\User;
use App\Models\UserAccount;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use AuthorizesAdministratorPermissions;

    public User $user;

    public UserAccountForm $form;

    public string $currencySearch = '';

    public function mount(User $user): void
    {
        $this->form->setUser($user);
    }

    #[On('panels.administrator.user-management.user.user-account.create.assign-data')]
    public function assignData(): void
    {
        $this->authorizePermission('user-management.user.edit');

        $this->form->setUser($this->user);
        $this->currencySearch = '';
        $this->resetValidation();

        Flux::modal('user-management.user.user-account.create')->show();
    }

    #[Computed]
    public function currencies(): Collection
    {
        return $this->form->availableCurrencies($this->currencySearch);
    }

    public function save(): void
    {
        $this->authorizePermission('user-management.user.edit');

        $this->form->user = $this->user;
        $this->form->store();

        $this->form->setUser($this->user);
        $this->currencySearch = '';

        $this->dispatch('panels.administrator.user-management.user.user-account.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.user_account_created'));
    }
};
?>

<flux:modal name="user-management.user.user-account.create" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.create') }} {{ __('general.user_account') }}</flux:heading>
        <flux:subheading>{{ $user->full_name }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:select wire:model="form.currency_id" variant="combobox" :filter="false" label="{{ __('general.currency') }}" placeholder="{{ __('general.currency') }}...">
            <x-slot name="input">
                <flux:select.input wire:model.live.debounce.300ms="currencySearch" placeholder="{{ __('general.currency') }}..." />
            </x-slot>

            @forelse ($this->currencies as $currency)
                <flux:select.option value="{{ $currency->id }}" wire:key="user-account-create-currency-{{ $currency->id }}">
                    <span dir="ltr">{{ $currency->symbol }}</span> — {{ $currency->name }}
                </flux:select.option>
            @empty
                <flux:select.option value="" disabled>{{ __('general.no_results_found') }}</flux:select.option>
            @endforelse
        </flux:select>

        <flux:select wire:model="form.type" variant="listbox" searchable label="{{ __('general.type') }}" placeholder="{{ __('general.type') }}...">
            @foreach (UserAccountTypeEnum::cases() as $accountType)
                <flux:select.option value="{{ $accountType->value }}">{{ $accountType->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model="form.account_number" label="{{ __('general.account_number') }}" icon="credit-card" placeholder="{{ __('general.account_number') }}..." dir="ltr" />

        <flux:input wire:model="form.account_owner" label="{{ __('general.account_owner') }}" icon="user" placeholder="{{ __('general.account_owner') }}..." />

        <flux:select wire:model="form.status" variant="listbox" searchable label="{{ __('general.status') }}">
            <flux:select.option value="{{ UserAccount::STATUS_PENDING }}">{{ __('general.statuses.pending') }}</flux:select.option>
            <flux:select.option value="{{ UserAccount::STATUS_APPROVED }}">{{ __('general.statuses.approved') }}</flux:select.option>
            <flux:select.option value="{{ UserAccount::STATUS_REJECTED }}">{{ __('general.statuses.rejected') }}</flux:select.option>
        </flux:select>

        <div class="flex items-center justify-between gap-3">
            <flux:label>{{ __('general.is_active') }}</flux:label>
            <flux:switch wire:model="form.is_active" />
        </div>

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
