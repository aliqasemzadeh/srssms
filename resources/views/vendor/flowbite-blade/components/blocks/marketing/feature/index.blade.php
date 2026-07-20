@props([
    'title' => '',
    'description' => '',
    'nbItems' => 3,
])

@php
    $cols = match((int) $nbItems) {
        1 => 'lg:grid-cols-1',
        2 => 'lg:grid-cols-2',
        4 => 'lg:grid-cols-4',
        default => 'lg:grid-cols-3',
    };
@endphp

<section {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900']) }}>
    <div class="py-8 px-4 mx-auto max-w-screen-xl sm:py-16 lg:px-6">
        @if ($title || $description)
            <div class="max-w-screen-md mb-8 lg:mb-16">
                @if ($title)
                    <h2 class="mb-4 text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="text-gray-500 sm:text-xl dark:text-gray-400">{{ $description }}</p>
                @endif
            </div>
        @endif
        <div class="space-y-8 md:grid md:grid-cols-2 {{ $cols }} md:gap-12 md:space-y-0">
            {{ $slot }}
        </div>
    </div>
</section>
