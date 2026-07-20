<div id="{{ $id }}" {{ $attributes->class([$baseClasses()]) }} role="alert">
    @if (isset($icon))
        {{ $icon }}
    @elseif ($fwbIcon)
        <x-dynamic-component :component="'fwb-' . $fwbIcon" class="shrink-0 w-4 h-4" />
    @endif

    <div class="ms-3 text-sm font-medium">
        {{ $slot }}
    </div>

    @if ($dismissible)
        <button type="button" class="ms-auto -mx-1.5 -my-1.5 rounded-base focus:ring-2 p-1.5 inline-flex items-center justify-center h-8 w-8 {{ $dismissButtonClasses() }}" data-dismiss-target="#{{ $id }}" aria-label="Close">
            <span class="sr-only">Close</span>
            <x-fwb-o-close class="w-3 h-3" />
        </button>
    @endif
</div>
