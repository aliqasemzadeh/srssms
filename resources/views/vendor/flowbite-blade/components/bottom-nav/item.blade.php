<a href="{{ $href }}" class="inline-flex flex-col items-center justify-center px-5 hover:bg-neutral-secondary-soft group {{ $active ? 'text-fg-brand-strong' : '' }}" {{ $attributes }}>
    @if($icon)
        {!! $icon !!}
    @elseif($fwbIcon)
        <x-dynamic-component :component="'fwb-' . $fwbIcon" class="w-5 h-5 mb-1 text-body group-hover:text-fg-brand-strong" />
    @endif
    @if($label)
        <span class="text-sm text-body group-hover:text-fg-brand-strong">{{ $label }}</span>
    @endif
</a>
