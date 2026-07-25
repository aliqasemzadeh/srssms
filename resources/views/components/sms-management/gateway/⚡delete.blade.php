<?php

use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Models\Sms\Gateway;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use AuthorizesAdministratorPermissions;

    public ?Gateway $gateway = null;

    #[On('panels.administrator.sms-management.gateway.delete.assign-data')]
    public function assignData(int $gateway): void
    {
        $this->authorizePermission('sms-management.gateway.delete');

        $this->gateway = Gateway::query()->findOrFail($gateway);

        Flux::modal('sms-management.gateway.delete')->show();
    }

    public function delete(): void
    {
        $this->authorizePermission('sms-management.gateway.delete');

        if (! $this->gateway) {
            return;
        }

        $this->gateway->delete();
        $this->gateway = null;
        $this->dispatch('panels.administrator.sms-management.gateway.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.sms_gateway_deleted'));
    }
};
?>

<flux:modal name="sms-management.gateway.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>
        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($gateway)
        <flux:callout icon="radio-tower" variant="secondary" inline>
            <flux:callout.heading>
                {{ $gateway->title }} — <span dir="ltr">{{ $gateway->number }}</span>
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
