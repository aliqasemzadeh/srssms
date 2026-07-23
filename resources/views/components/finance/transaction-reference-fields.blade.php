@props([
    'referenceType' => '',
    'referenceOptions' => collect(),
    'referenceSearchModel' => 'referenceSearch',
])

@php
    $references = config('finance.transaction_references', []);
@endphp

<div class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
    <div>
        <flux:heading size="sm">{{ __('general.reference') }}</flux:heading>
        <flux:text class="text-sm text-zinc-500">{{ __('general.reference_edit_hint') }}</flux:text>
    </div>

    <flux:select
        wire:model.live="form.reference_type"
        variant="listbox"
        searchable
        label="{{ __('general.reference_type') }}"
        placeholder="{{ __('general.reference_type') }}..."
        clearable
    >
        @foreach ($references as $key => $reference)
            <flux:select.option value="{{ $key }}">{{ __($reference['label'] ?? $key) }}</flux:select.option>
        @endforeach
    </flux:select>

    @if (filled($referenceType))
        <flux:select
            wire:model="form.reference_id"
            variant="combobox"
            :filter="false"
            label="{{ __('general.reference') }}"
            placeholder="{{ __('general.reference') }}..."
            clearable
        >
            <x-slot name="input">
                <flux:select.input wire:model.live.debounce.300ms="{{ $referenceSearchModel }}" placeholder="{{ __('general.search') }}..." />
            </x-slot>

            @forelse ($referenceOptions as $option)
                <flux:select.option value="{{ $option->id }}" wire:key="transaction-reference-{{ $referenceType }}-{{ $option->id }}">
                    {{ $option->label }}
                </flux:select.option>
            @empty
                <flux:select.option value="" disabled>{{ __('general.no_results_found') }}</flux:select.option>
            @endforelse
        </flux:select>
    @endif
</div>
