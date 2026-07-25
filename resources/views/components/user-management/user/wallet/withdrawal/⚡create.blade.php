<?php

use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Enums\WithdrawalStatusEnum;
use App\Livewire\Forms\WithdrawalForm;
use App\Models\Finance\Wallet;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use AuthorizesAdministratorPermissions;

    public User $user;

    public Wallet $wallet;

    public WithdrawalForm $form;

    public string $userAccountSearch = '';

    public function mount(User $user, Wallet $wallet): void
    {
        abort_unless($wallet->user_id === $user->id, 404);

        $this->user = $user;
        $this->wallet = $wallet->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);
        $this->form->setDefaults($this->user, $this->wallet);
    }

    #[On('panels.administrator.user-management.user.wallet.withdrawal.create.assign-data')]
    public function assignData(): void
    {
        $this->authorizePermission('finance-management.withdrawal.create');

        $this->wallet->refresh()->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);
        $this->form->setDefaults($this->user, $this->wallet);
        $this->userAccountSearch = '';
        $this->resetValidation();
        unset($this->userAccounts);

        Flux::modal('user-management.user.wallet.withdrawal.create')->show();
    }

    public function updatedUserAccountSearch(): void
    {
        unset($this->userAccounts);
    }

    #[Computed]
    public function userAccounts(): Collection
    {
        return $this->form->userAccountOptions($this->userAccountSearch)
            ->map(fn ($account) => (object) [
                'id' => $account->id,
                'label' => $this->form->userAccountOptionLabel($account),
            ]);
    }

    public function save(): void
    {
        $this->authorizePermission('finance-management.withdrawal.create');

        $this->form->user_id = (string) $this->user->id;
        $this->form->wallet_id = (string) $this->wallet->id;
        $this->form->store();

        $this->form->setDefaults($this->user, $this->wallet);
        $this->userAccountSearch = '';
        unset($this->userAccounts);

        $this->dispatch('panels.administrator.user-management.user.wallet.withdrawal.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.withdrawal_created'));
    }
};
?>

@php
    $withdrawalMethods = config('finance.withdrawal_methods', []);
    $decimals = $form->decimals();
    $currencySymbol = $wallet->currency?->symbol ?? '';
@endphp

<flux:modal name="user-management.user.wallet.withdrawal.create" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.create') }} {{ __('general.withdrawal') }}</flux:heading>
        <flux:subheading>
            <span dir="ltr">{{ $currencySymbol }}</span> — {{ $user->full_name }}
        </flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:select wire:model="form.user_account_id" variant="combobox" :filter="false" label="{{ __('general.user_account') }}">
            <x-slot name="input">
                <flux:select.input wire:model.live.debounce.300ms="userAccountSearch" placeholder="{{ __('general.search') }}..." />
            </x-slot>

            @foreach ($this->userAccounts as $accountOption)
                <flux:select.option value="{{ $accountOption->id }}" wire:key="user-withdrawal-create-account-{{ $accountOption->id }}">
                    <span dir="ltr">{{ $accountOption->label }}</span>
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:field>
            <flux:label>{{ __('general.amount') }}</flux:label>
            <flux:description>{{ __('general.amount_decimals_hint', ['decimals' => $decimals]) }}</flux:description>
            <flux:input wire:model="form.amount" dir="ltr" x-mask:dynamic="{{ $form->moneyMaskExpression() }}" />
            <flux:error name="form.amount" />
        </flux:field>

        <div class="grid gap-3 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('general.fee') }}</flux:label>
                <flux:input wire:model="form.fee" dir="ltr" x-mask:dynamic="{{ $form->moneyMaskExpression() }}" />
                <flux:error name="form.fee" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('general.tax') }}</flux:label>
                <flux:input wire:model="form.tax" dir="ltr" x-mask:dynamic="{{ $form->moneyMaskExpression() }}" />
                <flux:error name="form.tax" />
            </flux:field>
        </div>

        <flux:select wire:model="form.method" variant="listbox" searchable label="{{ __('general.method') }}">
            @foreach ($withdrawalMethods as $methodKey => $methodLabel)
                <flux:select.option value="{{ $methodKey }}">{{ __($methodLabel) }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model="form.tracking_code" label="{{ __('general.tracking_code') }}" dir="ltr" />

        <flux:select wire:model="form.status" variant="listbox" searchable label="{{ __('general.status') }}">
            @foreach (WithdrawalStatusEnum::cases() as $statusOption)
                <flux:select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:textarea wire:model="form.admin_note" label="{{ __('general.admin_note') }}" rows="3" />

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
