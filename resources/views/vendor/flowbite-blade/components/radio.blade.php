<div class="flex items-start">
    <div class="flex items-center h-5">
        <input id="{{ $id }}" type="radio" class="w-4 h-4 {{ $colorClasses() }} bg-neutral-secondary-medium border-default-medium ring-offset-buffer focus:ring-2" {{ $name ? "name={$name}" : '' }} {{ $checked ? 'checked' : '' }} {{ $disabled ? 'disabled' : '' }} {{ $required ? 'required' : '' }} {{ $attributes }}>
    </div>
    @if($label || $helper)
        <div class="ms-2 text-sm">
            @if($label)
                <label for="{{ $id }}" class="font-medium text-heading {{ $disabled ? 'opacity-50' : '' }}">{{ $label }}</label>
            @endif
            @if($helper)
                <p class="text-xs font-normal text-body">{{ $helper }}</p>
            @endif
        </div>
    @endif
</div>
