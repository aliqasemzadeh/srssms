<{{ $resolvedTag() }}
    @if ($href) href="{{ $href }}" @endif
    @if ($resolvedTag() === 'button' && !$attributes->has('type')) type="button" @endif
    @if ($disabled) disabled @endif
    {{ $attributes->class([$allClasses()]) }}
>@if(isset($icon)){{ $icon }}@elseif($fwbIcon)<x-dynamic-component :component="'fwb-' . $fwbIcon" class="w-3.5 h-3.5 me-2" />@endif{{ $slot }}</{{ $resolvedTag() }}>
