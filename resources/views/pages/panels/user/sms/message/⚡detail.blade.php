<?php

use App\Enums\Sms\SmsDirectionEnum;
use App\Models\Sms\Message;
use App\Services\Sms\SmsDeliveryStatusSyncer;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public Message $message;

    public function mount(Message $message, SmsDeliveryStatusSyncer $syncer): void
    {
        abort_unless($message->user_id === Auth::id(), 404);

        $this->message = $message->load(['gateway.provider', 'recipients', 'token']);

        if ($this->message->direction === SmsDirectionEnum::Outbound) {
            $result = $syncer->sync($this->message);

            $this->message = $this->message->fresh(['gateway.provider', 'recipients', 'token']);

            if (! ($result['skipped'] ?? true) && ($result['updated'] ?? 0) > 0) {
                Flux::toast(__('general.sms_status_refreshed'));
            }
        }
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.message_details') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item href="{{ route('panels.user.sms.message.index') }}" wire:navigate>{{ __('general.sms_messages') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>#{{ $message->id }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:button variant="primary" color="zinc" icon="arrow-right" href="{{ route('panels.user.sms.message.index') }}" wire:navigate>
                {{ __('general.sms_messages') }}
            </flux:button>
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
                        <flux:text class="text-sm opacity-70">{{ __('general.source') }}</flux:text>
                        <flux:badge color="{{ $message->source->color() }}">{{ $message->source->label() }}</flux:badge>
                    </div>
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.number') }}</flux:text>
                        <div dir="ltr">{{ $message->number }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.sms_gateway') }}</flux:text>
                        <div>{{ $message->gateway?->title }} (<span dir="ltr">{{ $message->gateway?->number }}</span>)</div>
                    </div>
                    @if ($message->source->value === 'api')
                        <div>
                            <flux:text class="text-sm opacity-70">{{ __('general.sms_token') }}</flux:text>
                            <div>{{ $message->token?->name ?: '—' }}</div>
                        </div>
                    @endif
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.parts_count') }}</flux:text>
                        <div>{{ $message->parts_count }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.encoding') }}</flux:text>
                        <div>{{ $message->encoding->label() }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.cost') }}</flux:text>
                        <div>{{ $message->cost !== null ? number_format($message->cost).' '.__('general.rial') : '—' }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.created_at') }}</flux:text>
                        <div>{{ $message->created_at->toDynamicFormat('Y/m/d H:i:s') }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm opacity-70">{{ __('general.sent_at') }}</flux:text>
                        <div>{{ $message->sent_at?->toDynamicFormat('Y/m/d H:i:s') ?: '—' }}</div>
                    </div>
                </div>

                <div>
                    <flux:text class="mb-2 text-sm opacity-70">{{ __('general.final_message_preview') }}</flux:text>
                    <div class="relative overflow-hidden rounded-2xl border border-teal-200/80 bg-gradient-to-b from-teal-50 to-white p-4 shadow-sm dark:border-teal-900/50 dark:from-teal-950/40 dark:to-zinc-900">
                        <div class="rounded-xl bg-white/90 p-4 text-sm leading-7 text-zinc-800 shadow-inner whitespace-pre-wrap dark:bg-zinc-900/80 dark:text-zinc-100" dir="auto">{{ $message->body }}</div>
                    </div>
                </div>
            </flux:card>

            <div class="space-y-6">
                @if ($message->direction->value === 'outbound')
                    <flux:card class="space-y-4">
                        <flux:heading size="lg">{{ __('general.recipients') }}</flux:heading>

                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('general.mobile') }}</flux:table.column>
                                <flux:table.column>{{ __('general.reference_id') }}</flux:table.column>
                                <flux:table.column>{{ __('general.status') }}</flux:table.column>
                                <flux:table.column>{{ __('general.error') }}</flux:table.column>
                                <flux:table.column>{{ __('general.delivered_at') }}</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @forelse ($message->recipients as $recipient)
                                    <flux:table.row :key="$recipient->id">
                                        <flux:table.cell><span dir="ltr">{{ $recipient->mobile }}</span></flux:table.cell>
                                        <flux:table.cell><span dir="ltr">{{ $recipient->reference_id ?: '—' }}</span></flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge size="sm" color="{{ $recipient->status->color() }}">{{ $recipient->status->label() }}</flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell>{{ $recipient->error ?: '—' }}</flux:table.cell>
                                        <flux:table.cell>{{ $recipient->delivered_at?->toDynamicFormat('Y/m/d H:i') ?: '—' }}</flux:table.cell>
                                    </flux:table.row>
                                @empty
                                    <flux:table.row>
                                        <flux:table.cell colspan="5">—</flux:table.cell>
                                    </flux:table.row>
                                @endforelse
                            </flux:table.rows>
                        </flux:table>
                    </flux:card>
                @endif
            </div>
        </div>
    </div>
</div>
