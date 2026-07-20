@if ($outsideLabel || $outsideRightLabel)
    <div class="flex justify-between mb-1">
        @if ($outsideLabel)
            <span class="text-base font-medium text-body">{{ $outsideLabel }}</span>
        @endif
        <span class="text-sm font-medium text-body {{ !$outsideLabel ? 'ms-auto' : '' }}">{{ $outsideRightLabel ?? round($percentage()) . '%' }}</span>
    </div>
@endif
<div {{ $attributes->class([$containerClasses()]) }}>
    <div class="{{ $barClasses() }}" style="width: {{ $percentage() }}%">
        @if ($insideLabel)
            {{ $insideLabel }}
        @endif
    </div>
</div>
