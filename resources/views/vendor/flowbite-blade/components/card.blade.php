<{{ $resolvedTag() }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class([$baseClasses()]) }}
>
    @if ($img && $imgPosition === 'top')
        <img class="{{ $imgClasses() }}" src="{{ $img }}" alt="{{ $imgAlt }}" />
    @endif

    @if ($header ?? false)
        <div class="p-5 border-b border-default">
            {{ $header }}
        </div>
    @endif

    <div class="p-5">
        @if($title)
            <{{ $titleElement }} class="mb-2 text-2xl font-bold tracking-tight text-heading">{{ $title }}</{{ $titleElement }}>
            @if(isset($content))
                {{ $content }}
            @else
                <p class="font-normal text-body">{{ $slot }}</p>
            @endif
        @else
            {{ $slot }}
        @endif
    </div>

    @if ($footer ?? false)
        <div class="p-5 border-t border-default">
            {{ $footer }}
        </div>
    @endif

    @if ($img && $imgPosition === 'bottom')
        <img class="{{ $imgClasses() }}" src="{{ $img }}" alt="{{ $imgAlt }}" />
    @endif
</{{ $resolvedTag() }}>
