@props([
    'messages',
    'detailRoute',
    'variant' => 'user',
])

<div {{ $attributes->class('space-y-3 md:hidden') }}>
    @forelse ($messages as $message)
        <a
            href="{{ route($detailRoute, $message) }}"
            wire:navigate
            wire:key="sms-mobile-{{ $message->id }}"
            class="block rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600 dark:hover:bg-zinc-800/80"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1 space-y-2">
                    @if ($variant === 'admin')
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-medium tabular-nums" dir="ltr">{{ $message->number }}</span>
                            <flux:badge size="sm" color="{{ $message->direction->color() }}">{{ $message->direction->label() }}</flux:badge>
                        </div>
                        <p class="line-clamp-2 text-sm text-zinc-600 dark:text-zinc-300" dir="auto">
                            {{ \Illuminate\Support\Str::limit($message->body, 80) }}
                        </p>
                    @else
                        <p class="line-clamp-2 text-sm font-medium text-zinc-800 dark:text-zinc-100" dir="auto">
                            {{ \Illuminate\Support\Str::limit($message->body, 100) }}
                        </p>
                    @endif

                    <div class="flex flex-wrap items-center gap-1.5">
                        <flux:badge size="sm" color="{{ $message->status->color() }}">{{ $message->status->label() }}</flux:badge>

                        @if ($variant === 'user')
                            <flux:badge size="sm" color="{{ $message->source->color() }}">{{ $message->source->label() }}</flux:badge>
                            <flux:badge size="sm" color="sky">{{ __('general.recipients_count', ['count' => $message->recipients_count]) }}</flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc">{{ $message->parts_count }}</flux:badge>
                            <flux:badge size="sm" color="sky">{{ $message->encoding->label() }}</flux:badge>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                        <span>{{ $message->gateway?->title ?: '—' }}</span>
                        <span>{{ $message->created_at->toDynamicFormat($variant === 'admin' ? 'Y/m/d H:i:s' : 'Y/m/d H:i') }}</span>

                        @if ($variant === 'user')
                            <span>{{ $message->parts_count }} {{ __('general.parts_count') }}</span>
                            <span>{{ $message->cost !== null ? number_format($message->cost).' '.__('general.rial') : '—' }}</span>
                        @elseif ($message->user)
                            <span>{{ $message->user->full_name }}</span>
                        @endif
                    </div>
                </div>

                <flux:icon.eye variant="outline" class="mt-0.5 size-4 shrink-0 text-zinc-400" />
            </div>
        </a>
    @empty
        <div class="rounded-xl border border-dashed border-zinc-200 px-4 py-8 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
            —
        </div>
    @endforelse
</div>
