<div {{ $attributes }}>
    <ul class="{{ $variantClasses }}" id="tabs-{{ $id }}" role="tablist"
        @if($activeClasses) data-tabs-active-classes="{{ $activeClasses }}" @endif
        @if($inactiveClasses) data-tabs-inactive-classes="{{ $inactiveClasses }}" @endif
    >
        {{ $slot }}
    </ul>
</div>
