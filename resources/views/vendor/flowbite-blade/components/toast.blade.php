<div id="{{ $id }}" class="flex items-center w-full max-w-xs p-4 text-body bg-neutral-primary-soft rounded-base shadow-sm {{ $positionClasses }}" role="alert" {{ $attributes }}>
    @if(isset($icon) || $fwbIcon)
    <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 rounded-lg {{ $colorClasses }}">
        @if(isset($icon))
            {{ $icon }}
        @else
            <x-dynamic-component :component="'fwb-' . $fwbIcon" class="w-5 h-5" />
        @endif
    </div>
    @endif
    <div class="ms-3 text-sm font-normal">{{ $slot }}</div>
    @if($dismissible)
    <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-neutral-primary-soft text-body hover:text-heading rounded-base focus:ring-2 focus:ring-neutral-tertiary p-1.5 hover:bg-neutral-secondary-medium inline-flex items-center justify-center h-8 w-8" data-dismiss-target="#{{ $id }}" aria-label="Close">
        <span class="sr-only">Close</span>
        <x-fwb-o-close class="w-3 h-3" />
    </button>
    @endif
</div>
