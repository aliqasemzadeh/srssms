<?php

use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Models\Sms\Gateway;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use AuthorizesAdministratorPermissions;

    public ?Gateway $gateway = null;

    public ?User $user = null;

    #[On('panels.administrator.sms-management.gateway.user.delete.assign-data')]
    public function assignData(int $gateway, int $user): void
    {
        $this->authorizePermission('sms-management.gateway.user.delete');

        $this->gateway = Gateway::query()->findOrFail($gateway);
        $this->user = User::query()->findOrFail($user);

        Flux::modal('sms-management.gateway.user.delete')->show();
    }

    public function delete(): void
    {
        $this->authorizePermission('sms-management.gateway.user.delete');

        if (! $this->gateway || ! $this->user) {
            return;
        }

        $this->gateway->users()->detach($this->user->id);

        $this->gateway = null;
        $this->user = null;

        $this->dispatch('panels.administrator.sms-management.gateway.user.index.refresh');

        Flux::modals()->close();
        Flux::toast(__('general.gateway_access_revoked'));
    }
};
?>

<flux:modal name="sms-management.gateway.user.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>
        <flux:text class="mt-2">
            {{ __('general.revoke_access') }}
        </flux:text>
    </div>

    @if ($user)
        <flux:callout icon="user" variant="secondary" inline>
            <flux:callout.heading>{{ $user->full_name }}</flux:callout.heading>
            <flux:callout.text><span dir="ltr">{{ $user->mobile }}</span></flux:callout.text>
        </flux:callout>
    @endif

    <div class="flex gap-2">
        <flux:spacer />
        <flux:modal.close>
            <flux:button variant="ghost">{{ __('actions.cancel') }}</flux:button>
        </flux:modal.close>
        <flux:button wire:click="delete" variant="danger" icon="user-minus" icon:variant="outline">
            {{ __('general.revoke_access') }}
        </flux:button>
    </div>
</flux:modal>
