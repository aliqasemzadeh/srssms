<li>
    <a href="{{ $href }}" class="flex items-center p-2 text-heading rounded-base hover:bg-neutral-secondary-medium group {{ $active ? 'bg-neutral-secondary-medium' : '' }}" {{ $attributes }}>
        @if(isset($icon))
            {{ $icon }}
        @elseif($fwbIcon)
            <x-dynamic-component :component="'fwb-' . $fwbIcon" class="shrink-0 w-5 h-5 text-body transition duration-75 group-hover:text-heading" />
        @endif
        <span class="flex-1 ms-3 whitespace-nowrap">{{ $slot }}</span>
        @if($badge)
            <span class="{{ $badgeClassesComputed }}">{{ $badge }}</span>
        @endif
    </a>
</li>
