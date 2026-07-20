<div class="relative inline-block" {{ $attributes }}>
    <div class="inline-flex" data-tooltip-target="{{ $id }}" data-tooltip-trigger="{{ $triggerType }}" data-tooltip-placement="{{ $placement }}">
        {{ $triggerSlot ?? '' }}
    </div>
    <div id="{{ $id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium {{ $styleClasses }} rounded-lg shadow-sm opacity-0 transition-opacity duration-300 tooltip">
        {{ $slot }}
        <div class="tooltip-arrow" data-popper-arrow></div>
    </div>
</div>
