@props([
    'title' => '',
    'description' => '',
])

<section {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900']) }}>
    <div class="py-8 px-4 mx-auto max-w-screen-xl text-center lg:py-16 lg:px-6">
        @if ($title || $description)
            <div class="mx-auto mb-8 max-w-screen-sm lg:mb-16">
                @if ($title)
                    <h2 class="mb-4 text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="font-light text-gray-500 sm:text-xl dark:text-gray-400">{{ $description }}</p>
                @endif
            </div>
        @endif
        <div class="grid gap-8 lg:gap-16 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            {{ $slot }}
        </div>
    </div>
</section>
