<a href="{{ $href }}" class="flex items-center space-x-3 rtl:space-x-reverse" {{ $attributes }}>
    @if($src)
        <img src="{{ $src }}" class="h-8" alt="{{ $alt }}" />
    @endif
    <span class="self-center text-2xl font-semibold whitespace-nowrap text-heading">{{ $slot }}</span>
</a>
