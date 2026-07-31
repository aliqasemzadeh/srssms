@props([
    'recipients',
    'extended' => false,
])

<div {{ $attributes->class('space-y-3 md:hidden') }}>
    @forelse ($recipients as $recipient)
        <div
            wire:key="sms-recipient-mobile-{{ $recipient->id }}"
            class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700"
        >
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="font-medium tabular-nums" dir="ltr">{{ $recipient->mobile }}</span>
                <flux:badge size="sm" color="{{ $recipient->status->color() }}">{{ $recipient->status->label() }}</flux:badge>
            </div>

            <div class="mt-2 space-y-1.5 text-sm">
                <div class="flex flex-wrap gap-x-2 gap-y-1">
                    <flux:text class="text-xs opacity-70">{{ __('general.reference_id') }}:</flux:text>
                    <span class="break-all tabular-nums" dir="ltr">{{ $recipient->reference_id ?: '—' }}</span>
                </div>

                @if ($extended)
                    <div class="flex flex-wrap gap-x-2 gap-y-1">
                        <flux:text class="text-xs opacity-70">{{ __('general.delivered_at') }}:</flux:text>
                        <span>{{ $recipient->delivered_at?->toDynamicFormat('Y/m/d H:i') ?: '—' }}</span>
                    </div>

                    @if (filled($recipient->error))
                        <div class="space-y-0.5">
                            <flux:text class="text-xs opacity-70">{{ __('general.error') }}:</flux:text>
                            <p class="break-words text-rose-600 dark:text-rose-400">{{ $recipient->error }}</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-zinc-200 px-4 py-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
            —
        </div>
    @endforelse
</div>
