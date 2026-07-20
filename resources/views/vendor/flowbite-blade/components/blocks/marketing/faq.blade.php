@props([
    'title' => '',
])

<section {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900']) }}>
    <div class="py-8 px-4 mx-auto max-w-screen-xl sm:py-16 lg:px-6">
        @if ($title)
            <h2 class="mb-8 text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white">{{ $title }}</h2>
        @endif
        <div class="grid pt-8 text-left border-t border-gray-200 md:gap-16 dark:border-gray-700 md:grid-cols-2">
            {{ $slot }}
        </div>
    </div>
</section>
