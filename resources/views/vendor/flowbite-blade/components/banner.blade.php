<div id="{{ $id }}" tabindex="-1" class="flex justify-between items-center {{ $positionClasses }} p-4 {{ $borderClasses }} border-default {{ $colorClasses }}" {{ $attributes }}>
    <div class="flex items-center mx-auto">
        {{ $slot }}
    </div>
    @if($dismissible)
        <button data-dismiss-target="#{{ $id }}" type="button" class="flex-shrink-0 inline-flex justify-center w-7 h-7 items-center text-body hover:bg-neutral-secondary-medium hover:text-heading rounded-base text-sm p-1.5">
            <x-fwb-o-close class="w-3 h-3" />
            <span class="sr-only">Close banner</span>
        </button>
    @endif
</div>
