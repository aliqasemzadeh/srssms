<?php

use App\Livewire\Forms\Sms\TokenForm;
use App\Models\Sms\Token;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public TokenForm $form;

    #[On('panels.user.sms.token.edit.assign-data')]
    public function assignData(int $token): void
    {
        $model = Token::query()->where('user_id', Auth::id())->findOrFail($token);
        $this->form->setModel($model);
        $this->resetValidation();

        Flux::modal('sms.token.edit')->show();
    }

    public function save(): void
    {
        $this->form->update();
        $this->dispatch('panels.user.sms.token.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.sms_token_updated'));
    }

    public function regenerate(): void
    {
        $this->form->update(regenerate: true);
        $this->dispatch('panels.user.sms.token.index.refresh');
        Flux::toast(__('general.sms_token_regenerated'));
    }
};
?>

<flux:modal name="sms.token.edit" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.edit') }} {{ __('general.sms_token') }}</flux:heading>
        <flux:subheading>{{ __('general.sms_tokens') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        @if ($form->token)
            <div class="space-y-2">
                <flux:input
                    :value="$form->token->token"
                    label="{{ __('general.sms_token_value') }}"
                    readonly
                    copyable
                    input:class="text-left font-mono"
                    dir="ltr"
                />
                <flux:button
                    type="button"
                    size="sm"
                    variant="primary"
                    color="amber"
                    icon="refresh-cw"
                    wire:click="regenerate"
                    wire:confirm="{{ __('general.sms_token_regenerate_confirm') }}"
                >
                    {{ __('general.regenerate_token') }}
                </flux:button>
            </div>
        @endif

        <flux:input wire:model="form.name" label="{{ __('general.name') }}" icon="hash" />
        <flux:textarea
            wire:model="form.allowed_ips"
            label="{{ __('general.allowed_ips') }}"
            description="{{ __('general.allowed_ips_hint') }}"
            rows="4"
            dir="ltr"
        />
        <flux:switch wire:model="form.is_active" label="{{ __('general.is_active') }}" />
        <flux:button type="submit" variant="primary" color="orange" class="w-full">{{ __('actions.save') }}</flux:button>
    </form>
</flux:modal>
