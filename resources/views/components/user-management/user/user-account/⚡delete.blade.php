<?php

use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Enums\UserAccountTypeEnum;
use App\Models\UserAccount;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use AuthorizesAdministratorPermissions;

    public ?UserAccount $userAccount = null;

    #[On('panels.administrator.user-management.user.user-account.delete.assign-data')]
    public function assignData(int $userAccount): void
    {
        $this->authorizePermission('user-management.user.edit');

        $this->userAccount = UserAccount::query()
            ->with([
                'currency' => fn ($query) => $query->withTrashed(),
            ])
            ->findOrFail($userAccount);

        Flux::modal('user-management.user.user-account.delete')->show();
    }

    public function delete(): void
    {
        $this->authorizePermission('user-management.user.edit');

        if (! $this->userAccount) {
            return;
        }

        $this->userAccount->delete();

        $this->userAccount = null;

        $this->dispatch('panels.administrator.user-management.user.user-account.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.user_account_deleted'));
    }
};
?>

<flux:modal name="user-management.user.user-account.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>

        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($userAccount)
        @php
            $currency = $userAccount->currency;
            $currencyLabel = ($currency && ! $currency->trashed()) ? $currency->name : __('general.deleted');
            $currencySymbol = $currency?->symbol ?? __('general.deleted');
            $typeLabel = $userAccount->type instanceof UserAccountTypeEnum
                ? $userAccount->type->label()
                : $userAccount->type;
        @endphp

        <flux:callout icon="credit-card" variant="secondary" inline>
            <flux:callout.heading>
                {{ $typeLabel }} — <span dir="ltr">{{ $userAccount->account_number }}</span>
            </flux:callout.heading>
            <flux:callout.text>
                <span dir="ltr">{{ $currencySymbol }}</span> — {{ $currencyLabel }}
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
