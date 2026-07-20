<li class="flex md:w-full items-center {{ $completed ? 'text-fg-brand-strong' : '' }} {{ $current ? 'text-fg-brand-strong' : '' }} sm:after:content-[''] after:w-full after:h-1 after:border-b after:border-default after:border-1 after:hidden sm:after:inline-block after:mx-6 xl:after:mx-10" {{ $attributes }}>
    <span class="flex items-center after:content-['/'] sm:after:hidden after:mx-2 after:text-body">
        @if($completed)
            <x-fwb-s-check-circle class="w-3.5 h-3.5 sm:w-4 sm:h-4 me-2.5" />
        @elseif(isset($icon))
            {{ $icon }}
        @elseif($fwbIcon)
            <x-dynamic-component :component="'fwb-' . $fwbIcon" class="w-3.5 h-3.5 sm:w-4 sm:h-4 me-2.5" />
        @endif
        @if($label)
            <span class="whitespace-nowrap">{{ $label }}</span>
        @endif
        {{ $slot }}
    </span>
</li>
