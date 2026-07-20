@props([
    'title' => 'Let\'s create more tools and ideas that brings us together.',
    'description' => 'Flowbite helps you connect with friends and communities of people who share your interests. Connecting with your friends and family as well as discovering new ones is easy with features like Groups.',
    'image' => 'https://flowbite.s3.amazonaws.com/blocks/marketing-ui/cta/cta-dashboard-mockup.svg',
    'imageDark' => 'https://flowbite.s3.amazonaws.com/blocks/marketing-ui/cta/cta-dashboard-mockup-dark.svg',
    'imageAlt' => 'dashboard image',
    'buttonText' => 'Get started',
    'buttonHref' => '#',
])

<section {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900']) }}>
    <div class="gap-8 items-center py-8 px-4 mx-auto max-w-screen-xl xl:gap-16 md:grid md:grid-cols-2 sm:py-16 lg:px-6">
        @if(isset($visual))
            {{ $visual }}
        @else
            <img class="w-full dark:hidden" src="{{ $image }}" alt="{{ $imageAlt }}">
            <img class="w-full hidden dark:block" src="{{ $imageDark }}" alt="{{ $imageAlt }}">
        @endif
        <div class="mt-4 md:mt-0">
            <h2 class="mb-4 text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white">{{ $title }}</h2>
            <p class="mb-6 font-light text-gray-500 md:text-lg dark:text-gray-400">{{ $description }}</p>
            @if(isset($actions))
                {{ $actions }}
            @else
                <a href="{{ $buttonHref }}" class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:focus:ring-blue-900">
                    {{ $buttonText }}
                    <x-fwb-o-arrow-right class="ml-2 -mr-1 w-5 h-5" />
                </a>
            @endif
        </div>
    </div>
</section>
