@props([
    'title' => '',
    'price' => '',
    'description' => '',
])

<section {{ $attributes->merge(['class' => 'bg-white py-8 antialiased dark:bg-gray-900 md:py-16']) }}>
    <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
        <div class="lg:grid lg:grid-cols-2 lg:gap-8 xl:gap-16">
            @if(isset($gallery))
                <div class="shrink-0 max-w-md lg:max-w-lg mx-auto">
                    {{ $gallery }}
                </div>
            @else
                <div class="shrink-0 max-w-md lg:max-w-lg mx-auto">
                    <img class="w-full dark:hidden" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front.svg" alt="" />
                    <img class="w-full hidden dark:block" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front-dark.svg" alt="" />
                </div>
            @endif

            <div class="mt-6 sm:mt-8 lg:mt-0">
                @if ($title)
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">
                        {{ $title }}
                    </h1>
                @endif
                <div class="mt-4 sm:items-center sm:gap-4 sm:flex">
                    @if ($price)
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white sm:text-3xl">
                            {{ $price }}
                        </p>
                    @endif

                    <div class="mt-2 flex items-center gap-2 sm:mt-0">
                        <div class="flex items-center gap-1">
                            @for($i = 0; $i < 5; $i++)
                                <x-fwb-s-star class="h-4 w-4 text-yellow-300" />
                            @endfor
                        </div>
                        <p class="text-sm font-medium leading-none text-gray-500 dark:text-gray-400">(5.0)</p>
                        <a href="#" class="text-sm font-medium leading-none text-gray-900 underline hover:no-underline dark:text-white">345 Reviews</a>
                    </div>
                </div>

                @if(isset($actions))
                    <div class="mt-6 sm:mt-8 sm:flex sm:items-center sm:gap-4">
                        {{ $actions }}
                    </div>
                @else
                    <div class="mt-6 sm:mt-8 sm:flex sm:items-center sm:gap-4">
                        <a href="#" class="flex items-center justify-center rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700" role="button">
                            <x-fwb-o-heart class="-ms-2 me-2 h-5 w-5" />
                            Add to favorites
                        </a>

                        <a href="#" class="mt-4 flex items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 sm:mt-0" role="button">
                            <x-fwb-o-cart class="-ms-2 me-2 h-5 w-5" />
                            Add to cart
                        </a>
                    </div>
                @endif

                <hr class="my-6 border-gray-200 dark:border-gray-800 md:my-8" />

                @if(isset($details))
                    {{ $details }}
                @else
                    @if($description)
                        <p class="mb-6 text-gray-500 dark:text-gray-400">{{ $description }}</p>
                    @else
                        <p class="mb-6 text-gray-500 dark:text-gray-400">
                            Studio quality three mic array for crystal clear calls and voice recordings. Six-speaker sound system for a remarkably robust and high-quality audio experience. Up to 256GB of ultrafast SSD storage.
                        </p>
                        <p class="text-gray-500 dark:text-gray-400">
                            Two Thunderbolt USB 4 ports and up to two USB 3 ports. Ultrafast Wi-Fi 6 and Bluetooth 5.0 wireless. Color matched Magic Mouse with Magic Keyboard or Magic Keyboard with Touch ID.
                        </p>
                    @endif
                @endif
            </div>
        </div>
    </div>
</section>
