@props([
    'title' => 'Powering innovation at <span class="font-extrabold">200,000+</span> companies worldwide',
    'description' => 'Track work across the enterprise through an open, collaborative platform. Link issues across Jira and ingest data from other software development tools, so your IT support and operations teams have richer contextual information to rapidly respond to requests, incidents, and changes.',
    'ctaText' => 'Learn more',
    'ctaHref' => '#',
])

<section {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900']) }}>
    <div class="py-8 px-4 mx-auto max-w-screen-xl lg:py-16 lg:px-6">
        <div class="max-w-screen-lg text-gray-500 sm:text-lg dark:text-gray-400">
            <h2 class="mb-4 text-4xl tracking-tight font-bold text-gray-900 dark:text-white">{!! $title !!}</h2>
            <p class="mb-4 font-light">{{ $description }}</p>
            {{ $slot }}
            @if(isset($cta))
                {{ $cta }}
            @else
                <a href="{{ $ctaHref }}" class="inline-flex items-center font-medium text-blue-600 hover:text-blue-800 dark:text-blue-500 dark:hover:text-blue-700">
                    {{ $ctaText }}
                    <x-fwb-o-chevron-right class="ml-1 w-6 h-6" />
                </a>
            @endif
        </div>
    </div>
</section>
