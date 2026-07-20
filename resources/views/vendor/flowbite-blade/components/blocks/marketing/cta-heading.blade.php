@props([
    'title' => 'Start your free trial today',
    'description' => 'Try Flowbite Platform for 30 days. No credit card required.',
    'buttonText' => 'Free trial for 30 days',
    'buttonHref' => '#',
])

<section {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900']) }}>
    <div class="py-8 px-4 mx-auto max-w-screen-xl sm:py-16 lg:px-6">
        <div class="mx-auto max-w-screen-sm text-center">
            <h2 class="mb-4 text-4xl tracking-tight font-extrabold leading-tight text-gray-900 dark:text-white">{{ $title }}</h2>
            <p class="mb-6 font-light text-gray-500 dark:text-gray-400 md:text-lg">{{ $description }}</p>
            @if(isset($actions))
                {{ $actions }}
            @else
                <a href="{{ $buttonHref }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:focus:ring-blue-900">
                    {{ $buttonText }}
                </a>
            @endif
        </div>
    </div>
</section>
