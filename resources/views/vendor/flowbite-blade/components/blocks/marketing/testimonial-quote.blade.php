@props([
    'quote' => 'Flowbite is just awesome. It contains huge collection of prebuilt components and pages starting from login screen to complex dashboard. Perfect choice for your next SaaS application.',
    'name' => 'Micheal Gough',
    'role' => 'CEO at Google',
    'avatar' => 'https://flowbite.s3.amazonaws.com/blocks/marketing-ui/avatars/michael-gouch.png',
])

<section {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900']) }}>
    <div class="max-w-screen-xl px-4 py-8 mx-auto text-center lg:py-16 lg:px-6">
        <figure class="max-w-screen-md mx-auto">
            <x-fwb-o-quote class="h-12 mx-auto mb-3 text-gray-400 dark:text-gray-600" />
            <blockquote>
                <p class="text-2xl font-medium text-gray-900 dark:text-white">{{ $quote }}</p>
            </blockquote>
            <figcaption class="flex items-center justify-center mt-6 space-x-3">
                <img class="w-6 h-6 rounded-full" src="{{ $avatar }}" alt="profile picture">
                <div class="flex items-center divide-x-2 divide-gray-500 dark:divide-gray-700">
                    <div class="pr-3 font-medium text-gray-900 dark:text-white">{{ $name }}</div>
                    <div class="pl-3 text-sm font-light text-gray-500 dark:text-gray-400">{{ $role }}</div>
                </div>
            </figcaption>
        </figure>
    </div>
</section>