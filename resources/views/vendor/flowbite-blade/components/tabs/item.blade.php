<li class="{{ $variant === 'full' ? 'w-full' : 'me-2' }}" role="presentation">
    <button class="{{ $tabClasses }}" id="tab-{{ $id }}" type="button" role="tab" aria-selected="{{ $active ? 'true' : 'false' }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes }}>
        @if(isset($icon) || $fwbIcon)
            <span class="inline-flex items-center justify-center">
                @if(isset($icon))
                    {{ $icon }}
                @else
                    <x-dynamic-component :component="'fwb-' . $fwbIcon" class="w-4 h-4 me-2 text-body-subtle" />
                @endif
                {{ $slot }}
            </span>
        @else
            {{ $slot }}
        @endif
    </button>
</li>
