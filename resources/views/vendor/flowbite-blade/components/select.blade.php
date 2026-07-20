<div>
    @if($label)
        <label for="{{ $id }}" class="block mb-2 text-sm font-medium text-heading">{{ $label }}</label>
    @endif
    <select id="{{ $id }}" class="{{ $selectClasses }}" {{ $disabled ? 'disabled' : '' }} {{ $required ? 'required' : '' }} {{ $multiple ? 'multiple' : '' }} {{ $attributes }}>
        @if($placeholder)
            <option value="" disabled selected>{{ $placeholder }}</option>
        @endif
        @foreach($options as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
        @endforeach
        {{ $slot }}
    </select>
    @if($helper)
        <p class="mt-2 text-sm text-body">{{ $helper }}</p>
    @endif
</div>
