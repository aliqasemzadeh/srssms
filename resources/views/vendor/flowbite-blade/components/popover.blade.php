<div {{ $attributes }}>
    <div class="inline-flex" data-popover-target="{{ $id }}" data-popover-trigger="{{ $triggerType }}" data-popover-placement="{{ $placement }}" @if($offset) data-popover-offset="{{ $offset }}" @endif>
        {{ $triggerSlot ?? '' }}
    </div>
    <div data-popover id="{{ $id }}" role="tooltip" class="absolute z-10 invisible inline-block w-64 text-sm text-body transition-opacity duration-300 bg-neutral-primary-soft border border-default rounded-base shadow-sm opacity-0">
        @if($title ?? false)
        <div class="px-3 py-2 bg-neutral-secondary-medium border-b border-default rounded-t-base">
            <h3 class="font-semibold text-heading">{{ $title }}</h3>
        </div>
        @endif
        <div class="px-3 py-2">
            {{ $slot }}
        </div>
        @if($arrow)
        <div data-popper-arrow></div>
        @endif
    </div>
</div>
