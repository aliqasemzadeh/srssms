<div {{ $attributes }}>
    @if($trigger ?? false)
        <div class="inline-flex" data-dropdown-toggle="{{ $id }}" data-dropdown-placement="{{ $placement }}" @if($triggerType === 'hover') data-dropdown-trigger="hover" @endif>
            {{ $trigger }}
        </div>
    @else
        <button id="btn-{{ $id }}" data-dropdown-toggle="{{ $id }}" data-dropdown-placement="{{ $placement }}" @if($triggerType === 'hover') data-dropdown-trigger="hover" @endif class="{{ $inline ? 'inline-flex items-center text-body hover:bg-neutral-secondary-medium rounded-base text-sm p-1.5' : 'text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:outline-none focus:ring-brand-medium font-medium rounded-base text-sm px-5 py-2.5 text-center inline-flex items-center' }}" type="button">
            {{ $label }}
            <x-fwb-o-chevron-down class="w-2.5 h-2.5 ms-3" />
        </button>
    @endif
    <div id="{{ $id }}" class="z-10 hidden bg-neutral-primary-soft divide-y divide-default rounded-base shadow-sm w-44">
        @if($header ?? false)
        <div class="px-4 py-3 text-sm text-heading">
            {{ $header }}
        </div>
        @endif
        <ul class="py-2 text-sm text-heading" aria-labelledby="btn-{{ $id }}">
            {{ $slot }}
        </ul>
        @if($footer ?? false)
        <div class="py-2">
            {{ $footer }}
        </div>
        @endif
    </div>
</div>
