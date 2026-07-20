@aware(['striped' => false, 'hoverable' => false])

@php
    $classes = ['border-b border-default'];

    if ($striped) {
        $classes[] = 'odd:bg-neutral-primary even:bg-neutral-secondary-soft';
    } else {
        $classes[] = 'bg-neutral-primary';
    }

    if ($hoverable) {
        $classes[] = 'hover:bg-neutral-secondary-medium';
    }
@endphp

<tr class="{{ implode(' ', $classes) }}" {{ $attributes }}>
    {{ $slot }}
</tr>
