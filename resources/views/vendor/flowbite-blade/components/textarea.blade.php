<div>
    @if($label)
        <label for="{{ $id }}" class="block mb-2 text-sm font-medium {{ $labelClasses }}">{{ $label }}</label>
    @endif
    @if($footer ?? false)
        <div class="w-full border border-default rounded-base bg-neutral-secondary-soft">
            <div class="px-4 py-2 bg-neutral-primary-soft rounded-t-base">
                <textarea id="{{ $id }}" rows="{{ $rows }}" class="block w-full px-0 text-sm text-heading bg-neutral-primary-soft border-0 focus:ring-0" placeholder="{{ $placeholder }}" {{ $disabled ? 'disabled' : '' }} {{ $required ? 'required' : '' }} {{ $attributes }}>{{ $slot }}</textarea>
            </div>
            <div class="flex items-center justify-between px-3 py-2 border-t border-default">
                {{ $footer }}
            </div>
        </div>
    @else
        <textarea id="{{ $id }}" rows="{{ $rows }}" class="block p-2.5 w-full text-sm rounded-base border {{ $colorClasses }}" placeholder="{{ $placeholder }}" {{ $disabled ? 'disabled' : '' }} {{ $required ? 'required' : '' }} {{ $attributes }}>{{ $slot }}</textarea>
    @endif
    @if($helper)
        <p class="mt-2 text-sm {{ $helperClasses }}">{{ $helper }}</p>
    @endif
</div>
