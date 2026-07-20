<li class="flex items-center space-x-3">
    @if ($included)
        <x-fwb-o-check class="flex-shrink-0 w-5 h-5 text-success" />
        <span>{{ $slot }}</span>
    @else
        <x-fwb-o-close class="flex-shrink-0 w-5 h-5 text-body-subtle" />
        <span class="line-through decoration-body-subtle">{{ $slot }}</span>
    @endif
</li>
