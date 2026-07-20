@props([
    'title' => '',
])

<section {{ $attributes->merge(['class' => 'bg-white py-8 antialiased dark:bg-gray-900 md:py-16']) }}>
    <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
        @if ($title)
            <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl md:mb-8">{{ $title }}</h2>
        @endif

        @if($slot->isEmpty())
            <div class="mb-4 grid gap-4 sm:grid-cols-2 md:mb-8 lg:grid-cols-3 xl:grid-cols-4">
                {{-- Product card 1 --}}
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="h-56 w-full">
                        <a href="#">
                            <img class="mx-auto h-full dark:hidden" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front.svg" alt="" />
                            <img class="mx-auto hidden h-full dark:block" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front-dark.svg" alt="" />
                        </a>
                    </div>
                    <div class="pt-6">
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <span class="me-2 rounded bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">Up to 35% off</span>
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                    <span class="sr-only">Quick look</span>
                                    <x-fwb-o-eye class="h-5 w-5" />
                                </button>
                                <button type="button" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                    <span class="sr-only">Add to Favorites</span>
                                    <x-fwb-o-heart class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                        <a href="#" class="text-lg font-semibold leading-tight text-gray-900 hover:underline dark:text-white">Apple iMac 27", 1TB HDD, Retina 5K Display, M3 Max</a>
                        <div class="mt-2 flex items-center gap-2">
                            <div class="flex items-center">
                                @for($i = 0; $i < 5; $i++)
                                    <x-fwb-s-star class="h-4 w-4 text-yellow-400" />
                                @endfor
                            </div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">5.0</p>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">(455)</p>
                        </div>
                        <ul class="mt-2 flex items-center gap-4">
                            <li class="flex items-center gap-2">
                                <x-fwb-o-truck class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Fast Delivery</p>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-fwb-o-dollar class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Best Price</p>
                            </li>
                        </ul>
                        <div class="mt-4 flex items-center justify-between gap-4">
                            <p class="text-2xl font-extrabold leading-tight text-gray-900 dark:text-white">$1,699</p>
                            <button type="button" class="inline-flex items-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <x-fwb-o-cart class="-ms-2 me-2 h-5 w-5" />
                                Add to cart
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Product card 2 --}}
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="h-56 w-full">
                        <a href="#">
                            <img class="mx-auto h-full dark:hidden" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/ps5-light.svg" alt="" />
                            <img class="mx-auto hidden h-full dark:block" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/ps5-dark.svg" alt="" />
                        </a>
                    </div>
                    <div class="pt-6">
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <span class="me-2 rounded bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">Up to 15% off</span>
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                    <span class="sr-only">Quick look</span>
                                    <x-fwb-o-eye class="h-5 w-5" />
                                </button>
                                <button type="button" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                    <span class="sr-only">Add to Favorites</span>
                                    <x-fwb-o-heart class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                        <a href="#" class="text-lg font-semibold leading-tight text-gray-900 hover:underline dark:text-white">Playstation 5 Console with Extra Controller</a>
                        <div class="mt-2 flex items-center gap-2">
                            <div class="flex items-center">
                                @for($i = 0; $i < 5; $i++)
                                    <x-fwb-s-star class="h-4 w-4 text-yellow-400" />
                                @endfor
                            </div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">4.9</p>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">(312)</p>
                        </div>
                        <ul class="mt-2 flex items-center gap-4">
                            <li class="flex items-center gap-2">
                                <x-fwb-o-truck class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Fast Delivery</p>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-fwb-o-dollar class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Best Price</p>
                            </li>
                        </ul>
                        <div class="mt-4 flex items-center justify-between gap-4">
                            <p class="text-2xl font-extrabold leading-tight text-gray-900 dark:text-white">$499</p>
                            <button type="button" class="inline-flex items-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <x-fwb-o-cart class="-ms-2 me-2 h-5 w-5" />
                                Add to cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{ $slot }}
        @endif
    </div>
</section>
