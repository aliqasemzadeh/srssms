<li>
    @if($href)
        <a href="{{ $href }}" class="{{ $itemClass() }}" {{ $attributes }}>
            @if($icon){!! $icon !!}@elseif($fwbIcon)<x-dynamic-component :component="'fwb-' . $fwbIcon" class="w-4 h-4 me-2.5" />@endif
            {{ $slot }}
        </a>
    @elseif($button)
        <button type="button" class="{{ $itemClass() }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes }}>
            @if($icon){!! $icon !!}@elseif($fwbIcon)<x-dynamic-component :component="'fwb-' . $fwbIcon" class="w-4 h-4 me-2.5" />@endif
            {{ $slot }}
        </button>
    @else
        <span class="{{ $itemClass() }}" {{ $attributes }}>
            @if($icon){!! $icon !!}@elseif($fwbIcon)<x-dynamic-component :component="'fwb-' . $fwbIcon" class="w-4 h-4 me-2.5" />@endif
            {{ $slot }}
        </span>
    @endif
</li>
