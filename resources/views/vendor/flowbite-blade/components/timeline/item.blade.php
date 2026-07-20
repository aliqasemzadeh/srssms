@if($horizontal)
<li class="{{ $itemClass() }}" {{ $attributes }}>
    <div class="flex items-center">
        <div class="z-10 flex items-center justify-center w-6 h-6 bg-brand-softer rounded-full ring-0 ring-buffer sm:ring-8 shrink-0">
            @if(isset($icon))
                {{ $icon }}
            @elseif($fwbIcon)
                <x-dynamic-component :component="'fwb-' . $fwbIcon" class="w-2.5 h-2.5 text-fg-brand-strong" />
            @else
                <x-fwb-s-calendar-month class="w-2.5 h-2.5 text-fg-brand-strong" />
            @endif
        </div>
        <div class="hidden sm:flex w-full bg-neutral-tertiary h-0.5"></div>
    </div>
    <div class="mt-3 sm:pe-8">
        @if($title)
            <h3 class="text-lg font-semibold text-heading">{{ $title }}</h3>
        @endif
        @if($date)
            <time class="block mb-2 text-sm font-normal leading-none text-body">{{ $date }}</time>
        @endif
        <div class="text-base font-normal text-body">
            {{ $slot }}
        </div>
    </div>
</li>
@else
<li class="{{ $itemClass() }}" {{ $attributes }}>
    <span class="absolute flex items-center justify-center w-6 h-6 bg-brand-softer rounded-full -start-3 ring-8 ring-buffer">
        @if(isset($icon))
            {{ $icon }}
        @elseif($fwbIcon)
            <x-dynamic-component :component="'fwb-' . $fwbIcon" class="w-2.5 h-2.5 text-fg-brand-strong" />
        @else
            <x-fwb-s-calendar-month class="w-2.5 h-2.5 text-fg-brand-strong" />
        @endif
    </span>
    @if($title)
        <h3 class="flex items-center mb-1 text-lg font-semibold text-heading">{{ $title }}</h3>
    @endif
    @if($date)
        <time class="block mb-2 text-sm font-normal leading-none text-body">{{ $date }}</time>
    @endif
    <div class="mb-4 text-base font-normal text-body">
        {{ $slot }}
    </div>
</li>
@endif
