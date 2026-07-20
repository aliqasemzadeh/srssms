@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([$baseClasses()]) }}>{{ $slot }}</a>
@else
    <span id="{{ $id }}" {{ $attributes->class([$baseClasses(), 'gap-1']) }}>
        {{ $slot }}
        @if ($dismissible)
            <button type="button" class="inline-flex items-center p-1 ms-2 bg-transparent rounded-sm {{ $dismissButtonClasses() }}" data-dismiss-target="#{{ $id }}" aria-label="Remove">
                <x-fwb-o-close class="w-2 h-2" />
                <span class="sr-only">Remove badge</span>
            </button>
        @endif
    </span>
@endif
