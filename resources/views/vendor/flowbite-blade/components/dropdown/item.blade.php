@if($divider)
    <div class="my-1 border-t border-default"></div>
@endif
<li>
    @if($href)
        <a href="{{ $href }}" class="flex items-center px-4 py-2 hover:bg-neutral-secondary-medium" {{ $attributes }}>
            @if($icon){!! $icon !!}@elseif($fwbIcon)<x-dynamic-component :component="'fwb-' . $fwbIcon" class="w-4 h-4 me-2 text-body-subtle" />@endif
            {{ $slot }}
        </a>
    @else
        <button type="button" class="flex items-center w-full px-4 py-2 hover:bg-neutral-secondary-medium" {{ $attributes }}>
            @if($icon){!! $icon !!}@elseif($fwbIcon)<x-dynamic-component :component="'fwb-' . $fwbIcon" class="w-4 h-4 me-2 text-body-subtle" />@endif
            {{ $slot }}
        </button>
    @endif
</li>
