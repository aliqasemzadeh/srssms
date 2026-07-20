<div>
    @if($label)
        <label for="{{ $id }}" class="block mb-2 text-sm font-medium text-heading">{{ $label }}</label>
    @endif
    <input id="{{ $id }}" type="range" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}" value="{{ $value }}" class="w-full {{ $sizeClasses }} bg-neutral-tertiary rounded-base appearance-none cursor-pointer" {{ $disabled ? 'disabled' : '' }} {{ $required ? 'required' : '' }} {{ $attributes }}>
</div>
