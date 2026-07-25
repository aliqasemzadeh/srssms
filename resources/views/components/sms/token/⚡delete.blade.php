<?php

use App\Models\Sms\Token;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?Token $token = null;

    #[On('panels.user.sms.token.delete.assign-data')]
    public function assignData(int $token): void
    {
        $this->token = Token::query()->where('user_id', Auth::id())->findOrFail($token);
        Flux::modal('sms.token.delete')->show();
    }

    public function delete(): void
    {
        if (! $this->token) {
            return;
        }

        $this->token->delete();
        $this->token = null;
        $this->dispatch('panels.user.sms.token.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.sms_token_deleted'));
    }
};
?>

<flux:modal name="sms.token.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>
        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($token)
        <flux:callout icon="key" variant="secondary" inline>
            <flux:callout.heading>{{ $token->name }}</flux:callout.heading>
        </flux:callout>
    @endif

    <div class="flex gap-2">
        <flux:spacer />
        <flux:modal.close>
            <flux:button variant="ghost">{{ __('actions.cancel') }}</flux:button>
        </flux:modal.close>
        <flux:button wire:click="delete" variant="danger" icon="trash" icon:variant="outline">{{ __('actions.delete') }}</flux:button>
    </div>
</flux:modal>
