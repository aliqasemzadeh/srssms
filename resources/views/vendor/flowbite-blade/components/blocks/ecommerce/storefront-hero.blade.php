@props([
    'title' => '',
    'description' => '',
    'primaryButtonText' => '',
    'primaryButtonHref' => '#',
])

<section {{ $attributes->merge(['class' => 'bg-white py-8 antialiased dark:bg-gray-900 md:py-16']) }}>
    <div class="mx-auto grid max-w-screen-xl px-4 pb-8 md:grid-cols-12 lg:gap-12 lg:pb-16 xl:gap-0">
        <div class="content-center justify-self-start md:col-span-7 md:text-start">
            @if ($title)
                <h1 class="mb-4 text-4xl font-extrabold leading-none tracking-tight dark:text-white md:max-w-2xl md:text-5xl xl:text-6xl">{!! $title !!}</h1>
            @endif
            @if ($description)
                <p class="mb-4 max-w-2xl text-gray-500 dark:text-gray-400 md:mb-12 md:text-lg lg:mb-5 lg:text-xl">{{ $description }}</p>
            @endif

            @if(isset($actions))
                {{ $actions }}
            @else
                @if ($primaryButtonText)
                    <a href="{{ $primaryButtonHref }}" class="inline-block rounded-lg bg-blue-700 px-6 py-3.5 text-center font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">{{ $primaryButtonText }}</a>
                @endif
            @endif
        </div>

        <div class="hidden md:col-span-5 md:mt-0 md:flex">
            @if(isset($image))
                {{ $image }}
            @else
                <img class="dark:hidden" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/girl-shopping-list.svg" alt="shopping illustration" />
                <img class="hidden dark:block" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/girl-shopping-list-dark.svg" alt="shopping illustration" />
            @endif
        </div>
    </div>

    @if($slot->isEmpty())
        <div class="mx-auto grid max-w-screen-xl grid-cols-2 gap-8 px-4 text-gray-500 dark:text-gray-400 sm:grid-cols-3 sm:gap-12 lg:grid-cols-6">
            <a href="#" class="flex items-center justify-center">
                <svg class="h-9 hover:text-gray-900 dark:hover:text-white" viewBox="0 0 86 29" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 8.42h4.85v12.3H0V8.42Zm2.43-2.86c-.83 0-1.45-.22-1.88-.67C.21 4.52 0 4 0 3.43c0-.57.21-1.04.55-1.42C.89 1.63 1.52 1.44 2.35 1.44s1.45.19 1.88.57c.43.38.65.85.65 1.42 0 .57-.22 1.09-.57 1.46-.35.37-.97.67-1.88.67Z" />
                </svg>
            </a>
        </div>
    @else
        {{ $slot }}
    @endif
</section>
