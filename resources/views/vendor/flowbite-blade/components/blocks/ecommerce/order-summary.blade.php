@props([
    'title' => '',
])

<section {{ $attributes->merge(['class' => 'bg-white py-8 antialiased dark:bg-gray-900 md:py-16']) }}>
    <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
        <div class="mx-auto max-w-3xl">
            @if ($title)
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">{{ $title }}</h2>
            @endif

            {{-- Order items --}}
            <div class="mt-6 space-y-4 border-b border-t border-gray-200 py-8 dark:border-gray-700 sm:mt-8">
                @if($slot->isEmpty())
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <a href="#" class="flex h-14 w-14 shrink-0 items-center">
                                <img class="h-auto max-h-full w-full dark:hidden" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front.svg" alt="product" />
                                <img class="hidden h-auto max-h-full w-full dark:block" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front-dark.svg" alt="product" />
                            </a>
                            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                                <a href="#" class="truncate text-base font-medium text-gray-900 hover:underline dark:text-white">PC system All in One APPLE iMac (2023)</a>
                                <p class="text-sm text-gray-500 dark:text-gray-400">x1</p>
                            </div>
                            <div class="flex items-end justify-end">
                                <p class="text-base font-bold text-gray-900 dark:text-white">$1,499</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <a href="#" class="flex h-14 w-14 shrink-0 items-center">
                                <img class="h-auto max-h-full w-full dark:hidden" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/ps5-light.svg" alt="product" />
                                <img class="hidden h-auto max-h-full w-full dark:block" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/ps5-dark.svg" alt="product" />
                            </a>
                            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                                <a href="#" class="truncate text-base font-medium text-gray-900 hover:underline dark:text-white">Sony Playstation 5 Digital Edition</a>
                                <p class="text-sm text-gray-500 dark:text-gray-400">x2</p>
                            </div>
                            <div class="flex items-end justify-end">
                                <p class="text-base font-bold text-gray-900 dark:text-white">$598</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <a href="#" class="flex h-14 w-14 shrink-0 items-center">
                                <img class="h-auto max-h-full w-full dark:hidden" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/apple-watch-light.svg" alt="product" />
                                <img class="hidden h-auto max-h-full w-full dark:block" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/apple-watch-dark.svg" alt="product" />
                            </a>
                            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                                <a href="#" class="truncate text-base font-medium text-gray-900 hover:underline dark:text-white">Apple Watch SE (2nd Gen)</a>
                                <p class="text-sm text-gray-500 dark:text-gray-400">x3</p>
                            </div>
                            <div class="flex items-end justify-end">
                                <p class="text-base font-bold text-gray-900 dark:text-white">$799</p>
                            </div>
                        </div>
                    </div>
                @else
                    {{ $slot }}
                @endif
            </div>

            {{-- Totals --}}
            <div class="mt-8">
                @if(isset($totals))
                    {{ $totals }}
                @else
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <dl class="flex items-center justify-between gap-4">
                                <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Original price</dt>
                                <dd class="text-base font-medium text-gray-900 dark:text-white">$6,592.00</dd>
                            </dl>

                            <dl class="flex items-center justify-between gap-4">
                                <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Savings</dt>
                                <dd class="text-base font-medium text-green-500">-$299.00</dd>
                            </dl>

                            <dl class="flex items-center justify-between gap-4">
                                <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Store Pickup</dt>
                                <dd class="text-base font-medium text-gray-900 dark:text-white">$99</dd>
                            </dl>

                            <dl class="flex items-center justify-between gap-4">
                                <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Tax</dt>
                                <dd class="text-base font-medium text-gray-900 dark:text-white">$799</dd>
                            </dl>
                        </div>

                        <dl class="flex items-center justify-between gap-4 border-t border-gray-200 pt-2 dark:border-gray-700">
                            <dt class="text-base font-bold text-gray-900 dark:text-white">Total</dt>
                            <dd class="text-base font-bold text-gray-900 dark:text-white">$7,191.00</dd>
                        </dl>
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="mt-8">
                @if(isset($actions))
                    {{ $actions }}
                @else
                    <div class="flex items-center justify-end gap-4">
                        <a href="#" class="text-sm font-medium text-blue-700 hover:underline dark:text-blue-500">Return to shopping</a>
                        <button type="submit" class="rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Send the order</button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
