<?php

use App\Livewire\Forms\Sms\TokenForm;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public TokenForm $form;

    public ?string $createdToken = null;

    #[On('panels.user.sms.token.create.assign-data')]
    public function assignData(): void
    {
        $this->form->reset();
        $this->form->is_active = true;
        $this->createdToken = null;
        $this->resetValidation();

        Flux::modal('sms.token.create')->show();
    }

    public function save(): void
    {
        $token = $this->form->store();
        $this->createdToken = $token->token;
        $this->form->reset();
        $this->form->is_active = true;
        $this->dispatch('panels.user.sms.token.index.refresh');
        Flux::toast(__('general.sms_token_created'));
    }

    public function closeCreated(): void
    {
        $this->createdToken = null;
        Flux::modals()->close();
    }
};
?>

<flux:modal name="sms.token.create" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.create') }} {{ __('general.sms_token') }}</flux:heading>
        <flux:subheading>{{ __('general.sms_tokens') }}</flux:subheading>
    </div>

    @if ($createdToken)
        <flux:callout icon="key" variant="success">
            <flux:callout.heading>{{ __('general.sms_token_value') }}</flux:callout.heading>
            <flux:callout.text>
                <span class="break-all font-mono" dir="ltr">{{ $createdToken }}</span>
            </flux:callout.text>
        </flux:callout>
        <flux:button variant="primary" color="zinc" class="w-full" wire:click="closeCreated">
            {{ __('actions.close') }}
        </flux:button>
    @else
        <form wire:submit="save" class="space-y-6">
            <flux:input wire:model="form.name" label="{{ __('general.name') }}" icon="hash" />
            <flux:textarea
                wire:model="form.allowed_ips"
                label="{{ __('general.allowed_ips') }}"
                description="{{ __('general.allowed_ips_hint') }}"
                rows="4"
                dir="ltr"
            />
            <flux:switch wire:model="form.is_active" label="{{ __('general.is_active') }}" />
            <flux:button type="submit" variant="primary" color="teal" class="w-full">{{ __('actions.save') }}</flux:button>
        </form>
    @endif
</flux:modal>
