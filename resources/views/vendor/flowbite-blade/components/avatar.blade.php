<div class="relative" {{ $attributes }}>
    @if ($src)
        <img class="{{ $sizeClasses() }} {{ $rounded ? 'rounded-full' : 'rounded-sm' }} {{ $bordered ? 'p-1 ring-2 ring-default' : '' }}" src="{{ $src }}" alt="{{ $alt }}">
    @elseif ($initials)
        <div class="relative inline-flex items-center justify-center {{ $sizeClasses() }} overflow-hidden bg-neutral-tertiary {{ $rounded ? 'rounded-full' : 'rounded-sm' }} {{ $bordered ? 'p-1 ring-2 ring-default' : '' }}">
            <span class="font-medium text-body {{ $initialsSizeClasses() }}">{{ $initials }}</span>
        </div>
    @else
        <div class="relative inline-flex items-center justify-center {{ $sizeClasses() }} overflow-hidden bg-neutral-secondary-medium {{ $rounded ? 'rounded-full' : 'rounded-sm' }} {{ $bordered ? 'p-1 ring-2 ring-default' : '' }}">
            <svg class="absolute w-[120%] h-[120%] text-body-subtle -bottom-[10%]" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
        </div>
    @endif

    @if ($status)
        <span class="absolute block w-3.5 h-3.5 border-2 border-buffer rounded-full {{ $statusClasses() }}"></span>
    @endif
</div>
