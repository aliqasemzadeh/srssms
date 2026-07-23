@props([
    'decimals' => 2,
    'currency' => null,
    'symbol' => '',
])

@php
    $decimals = max(0, (int) $decimals);
    $symbol = $symbol !== '' ? $symbol : ($currency?->symbol ?? '');
    $placeholder = $decimals > 0
        ? '0.'.str_repeat('0', min($decimals, 2))
        : '0';
@endphp

{{-- Force LTR for money entry on RTL layouts --}}
<div dir="ltr" class="w-full">
    <flux:input.group>
        <flux:input
            {{ $attributes->merge([
                'autocomplete' => 'off',
                'inputmode' => 'decimal',
                'placeholder' => $placeholder,
            ]) }}
            mask:dynamic="$money($input, '.', ',', {{ $decimals }})"
            input:class="font-mono text-start"
        >
            <x-slot name="icon">
                @if ($currency?->logo && (! method_exists($currency, 'trashed') || ! $currency->trashed()))
                    <img src="{{ asset('storage/'.$currency->logo) }}" alt="{{ $symbol }}" class="size-4 rounded object-contain" />
                @else
                    <flux:icon.coins variant="outline" class="size-4" />
                @endif
            </x-slot>
        </flux:input>

        @if (filled($symbol))
            <flux:input.group.suffix>
                <span dir="ltr" class="font-medium">{{ $symbol }}</span>
            </flux:input.group.suffix>
        @endif
    </flux:input.group>
</div>
