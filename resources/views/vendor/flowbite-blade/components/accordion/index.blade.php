@php
$containerClasses = match (true) {
    $separated => 'space-y-4',
    $flush => '',
    default => 'rounded-base border border-default overflow-hidden shadow-xs divide-y divide-default',
};
@endphp

<div
    id="{{ $id }}"
    data-accordion="{{ $dataAccordionValue() }}"
    data-active-classes="{{ $activeClasses ?? ($flush ? 'text-heading' : '') }}"
    data-inactive-classes="{{ $inactiveClasses ?? ($flush ? 'text-body' : '') }}"
    {{ $attributes->class([$containerClasses]) }}
>{{ $slot }}</div>
