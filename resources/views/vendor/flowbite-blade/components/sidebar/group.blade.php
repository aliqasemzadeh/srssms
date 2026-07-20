<li>
    <button type="button" class="flex items-center w-full p-2 text-base text-heading transition duration-75 rounded-base group hover:bg-neutral-secondary-medium" aria-controls="dropdown-{{ $id }}" data-collapse-toggle="dropdown-{{ $id }}">
        @if(isset($icon))
            {{ $icon }}
        @elseif($fwbIcon)
            <x-dynamic-component :component="'fwb-' . $fwbIcon" class="shrink-0 w-5 h-5 text-body transition duration-75 group-hover:text-heading" />
        @endif
        <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">{{ $label }}</span>
        <x-fwb-o-chevron-down class="w-3 h-3" />
    </button>
    <ul id="dropdown-{{ $id }}" class="hidden py-2 space-y-2">
        {{ $slot }}
    </ul>
</li>
