<div id="{{ $id }}" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 {{ $positionClass }} w-full md:inset-0 h-[calc(100%-1rem)] max-h-full" @if($static) data-modal-backdrop="static" @endif {{ $attributes }}>
    <div class="relative p-4 w-full {{ $sizeClass }} max-h-full">
        <div class="relative bg-neutral-primary-soft rounded-base shadow-sm">
            @if($header ?? false)
            <div class="flex items-center justify-between p-4 md:p-5 border-b border-default rounded-t">
                <h3 class="text-xl font-semibold text-heading">
                    {{ $header }}
                </h3>
                @if($dismissible)
                <button type="button" class="text-body bg-transparent hover:bg-neutral-secondary-medium hover:text-heading rounded-base text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-hide="{{ $id }}">
                    <x-fwb-o-close class="w-3 h-3" />
                    <span class="sr-only">Close modal</span>
                </button>
                @endif
            </div>
            @endif
            <div class="p-4 md:p-5 {{ $popup ? 'text-center' : '' }}">
                {{ $slot }}
            </div>
            @if($footer ?? false)
            <div class="flex items-center p-4 md:p-5 border-t border-default rounded-b">
                {{ $footer }}
            </div>
            @endif
        </div>
    </div>
</div>
