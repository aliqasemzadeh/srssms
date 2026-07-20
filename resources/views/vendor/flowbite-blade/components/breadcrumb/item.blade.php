<li class="inline-flex items-center">
    @if($home)
        @if($href)
            <a href="{{ $href }}" class="inline-flex items-center text-sm font-medium text-body hover:text-fg-brand-strong" {{ $attributes }}>
                <x-fwb-s-home class="w-3 h-3 me-2.5" />
                {{ $slot }}
            </a>
        @else
            <span class="inline-flex items-center text-sm font-medium text-body">
                <x-fwb-s-home class="w-3 h-3 me-2.5" />
                {{ $slot }}
            </span>
        @endif
    @else
        <div class="flex items-center">
            <x-fwb-o-chevron-right class="rtl:rotate-180 w-3 h-3 text-body mx-1" />
            @if($href)
                <a href="{{ $href }}" class="ms-1 text-sm font-medium text-body hover:text-fg-brand-strong md:ms-2" {{ $attributes }}>{{ $slot }}</a>
            @else
                <span class="ms-1 text-sm font-medium text-body md:ms-2" {{ $attributes }}>{{ $slot }}</span>
            @endif
        </div>
    @endif
</li>
