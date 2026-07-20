@props([
    'title' => '',
    'description' => '',
])

<section {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900']) }}>
    <div class="py-8 px-4 mx-auto max-w-screen-xl text-center lg:py-16 lg:px-6">
        @if ($title || $description)
            <div class="mx-auto max-w-screen-sm">
                @if ($title)
                    <h2 class="mb-4 text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="mb-8 font-light text-gray-500 lg:mb-16 sm:text-xl dark:text-gray-400">{{ $description }}</p>
                @endif
            </div>
        @endif
        <div class="grid mb-8 lg:mb-12 lg:grid-cols-2">
            {{ $slot }}
        </div>
    </div>
</section>
