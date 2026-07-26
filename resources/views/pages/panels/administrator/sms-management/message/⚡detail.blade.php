<?php

use App\Enums\Sms\SmsDirectionEnum;
use App\Enums\Sms\SmsMessageStatusEnum;
use App\Models\Sms\Message;
use App\Services\Sms\SmsSender;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Throwable;

new class extends Component
{
    public Message $message;

    public function mount(Message $message): void
    {
        $this->message = $message->load(['gateway.provider', 'user', 'recipients']);
    }

    #[Computed]
    public function canResend(): bool
    {
        if ($this->message->direction !== SmsDirectionEnum::Outbound) {
            return false;
        }

        if ($this->message->status === SmsMessageStatusEnum::Failed) {
            return true;
        }

        return $this->message->recipients->contains(
            fn ($recipient) => $recipient->status === SmsMessageStatusEnum::Failed
        );
    }

    public function resend(SmsSender $sender): void
    {
        if (! $this->canResend) {
            Flux::toast(variant: 'danger', text: __('general.sms_resend_not_allowed'));

            return;
        }

        try {
            $this->message = $sender->resend($this->message);
            unset($this->canResend);

            Flux::toast(__('general.sms_resent_successfully'));
        } catch (\RuntimeException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        } catch (Throwable $e) {
            Flux::toast(variant: 'danger', text: __('general.sms_resend_failed'));
        }
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.message_details') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.sms_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.sms-management.message.index') }}" wire:navigate>{{ __('general.sms_messages') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>#{{ $message->id }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <div class="flex flex-wrap items-center gap-2">
                @if ($this->canResend)
                    <flux:tooltip content="{{ __('general.resend_sms') }}">
                        <flux:button
                            variant="primary"
                            color="orange"
                            icon="refresh-cw"
                            wire:click="resend"
                            wire:confirm="{{ __('general.are_you_sure') }}"
                        >
                            {{ __('general.resend_sms') }}
                        </flux:button>
                    </flux:tooltip>
                @endif

                <flux:button variant="primary" color="zinc" icon="arrow-right" href="{{ route('panels.administrator.sms-management.message.index') }}" wire:navigate>
                    {{ __('general.sms_messages') }}
                </flux:button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('general.message_details') }}</flux:heading>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.direction') }}</flux:text>
                        <flux:badge color="{{ $message->direction->color() }}">{{ $message->direction->label() }}</flux:badge>
                    </div>
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.status') }}</flux:text>
                        <flux:badge color="{{ $message->status->color() }}">{{ $message->status->label() }}</flux:badge>
                    </div>
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.number') }}</flux:text>
                        <div dir="ltr">{{ $message->number }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.sms_gateway') }}</flux:text>
                        <div>{{ $message->gateway?->title }} (<span dir="ltr">{{ $message->gateway?->number }}</span>)</div>
                    </div>
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.provider') }}</flux:text>
                        <div>{{ $message->gateway?->provider?->name }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.user') }}</flux:text>
                        <div>{{ $message->user?->full_name ?: '—' }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.parts_count') }}</flux:text>
                        <div>{{ $message->parts_count }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.encoding') }}</flux:text>
                        <div>{{ $message->encoding->label() }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.reference_id') }}</flux:text>
                        <div dir="ltr">{{ $message->reference_id ?: '—' }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.created_at') }}</flux:text>
                        <div>{{ $message->created_at->toDynamicFormat('Y/m/d H:i:s') }}</div>
                    </div>
                </div>

                <div>
                    <flux:text class="mb-2 text-sm opacity-70">{{ __('general.sms_message') }}</flux:text>
                    <div class="rounded-lg border border-zinc-200 p-4 whitespace-pre-wrap dark:border-zinc-700">{{ $message->body }}</div>
                </div>
            </flux:card>

            <div class="space-y-6">
                @if ($message->direction->value === 'outbound')
                    <flux:card class="space-y-4">
                        <flux:heading size="lg">{{ __('general.recipients') }}</flux:heading>

                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('general.mobile') }}</flux:table.column>
                                <flux:table.column>{{ __('general.status') }}</flux:table.column>
                                <flux:table.column>{{ __('general.reference_id') }}</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @forelse ($message->recipients as $recipient)
                                    <flux:table.row :key="$recipient->id">
                                        <flux:table.cell><span dir="ltr">{{ $recipient->mobile }}</span></flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge size="sm" color="{{ $recipient->status->color() }}">{{ $recipient->status->label() }}</flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell><span dir="ltr">{{ $recipient->reference_id ?: '—' }}</span></flux:table.cell>
                                    </flux:table.row>
                                @empty
                                    <flux:table.row>
                                        <flux:table.cell colspan="3">—</flux:table.cell>
                                    </flux:table.row>
                                @endforelse
                            </flux:table.rows>
                        </flux:table>
                    </flux:card>
                @endif

                @if ($message->provider_payload)
                    <flux:card class="space-y-4">
                        <flux:heading size="lg">{{ __('general.provider_payload') }}</flux:heading>
                        <pre class="overflow-x-auto rounded-lg bg-zinc-100 p-4 text-xs dark:bg-zinc-800" dir="ltr">{{ json_encode($message->provider_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </flux:card>
                @endif
            </div>
        </div>
    </div>
</div>
