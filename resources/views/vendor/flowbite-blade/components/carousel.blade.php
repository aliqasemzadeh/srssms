<div id="{{ $id }}" class="relative w-full" data-carousel="{{ $carouselType() }}" {{ $attributes }}>
    <div class="relative h-56 overflow-hidden rounded-base md:h-96">
        @if($items)
            @foreach($items as $index => $item)
                <div class="hidden {{ $duration }} {{ $easing }}" data-carousel-item="{{ $index === 0 ? 'active' : '' }}">
                    @if(!empty($item['href']))
                        <a href="{{ $item['href'] }}">
                            <img src="{{ $item['img'] }}" class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="{{ $item['alt'] ?? '' }}">
                        </a>
                    @else
                        <img src="{{ $item['img'] }}" class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="{{ $item['alt'] ?? '' }}">
                    @endif
                </div>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </div>
    @if($indicators)
    <div class="absolute z-30 flex -translate-x-1/2 bottom-5 left-1/2 space-x-3 rtl:space-x-reverse">
        {{-- Indicators are auto-managed by Flowbite JS --}}
    </div>
    @endif
    @if($controls)
    <button type="button" class="absolute top-0 start-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none" data-carousel-prev>
        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none">
            <x-fwb-o-chevron-left class="w-4 h-4 text-white rtl:rotate-180" />
            <span class="sr-only">Previous</span>
        </span>
    </button>
    <button type="button" class="absolute top-0 end-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none" data-carousel-next>
        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none">
            <x-fwb-o-chevron-right class="w-4 h-4 text-white rtl:rotate-180" />
            <span class="sr-only">Next</span>
        </span>
    </button>
    @endif
</div>
