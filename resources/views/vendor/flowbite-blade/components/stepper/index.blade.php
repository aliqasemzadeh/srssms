@if($variant === 'vertical' || $variant === 'timeline')
<div class="{{ $containerClasses }}" {{ $attributes }}>
    {{ $slot }}
</div>
@else
<ol class="{{ $containerClasses }}" {{ $attributes }}>
    {{ $slot }}
</ol>
@endif
