<?php

use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Models\Sms\Provider;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use AuthorizesAdministratorPermissions;

    public ?Provider $provider = null;

    #[On('panels.administrator.sms-management.provider.delete.assign-data')]
    public function assignData(int $provider): void
    {
        $this->authorizePermission('sms-management.provider.delete');

        $this->provider = Provider::query()->findOrFail($provider);

        Flux::modal('sms-management.provider.delete')->show();
    }

    public function delete(): void
    {
        $this->authorizePermission('sms-management.provider.delete');

        if (! $this->provider) {
            return;
        }

        $this->provider->delete();
        $this->provider = null;
        $this->dispatch('panels.administrator.sms-management.provider.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.provider_deleted'));
    }
};
?>

<flux:modal name="sms-management.provider.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>
        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($provider)
        <flux:callout icon="building-2" variant="secondary" inline>
            <flux:callout.heading>{{ $provider->name }}</flux:callout.heading>
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
