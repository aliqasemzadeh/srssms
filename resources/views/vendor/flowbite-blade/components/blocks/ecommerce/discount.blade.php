@props([
    'title' => '',
    'description' => '',
    'code' => '',
    'buttonText' => '',
    'buttonHref' => '#',
])

<section {{ $attributes->merge(['class' => 'bg-white py-8 antialiased dark:bg-gray-900 md:py-16']) }}>
    <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800 md:p-8 lg:flex lg:items-center lg:gap-16">
            <div class="hidden shrink-0 lg:block">
                <img class="dark:hidden" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/girl-shopping-list.svg" alt="discount illustration" />
                <img class="hidden dark:block" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/girl-shopping-list-dark.svg" alt="discount illustration" />
            </div>

            <div class="mt-4 w-full lg:mt-0">
                @if ($title)
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="mt-2 text-base font-normal text-gray-500 dark:text-gray-400">{{ $description }}</p>
                @endif

                <div class="mt-4 sm:flex sm:items-center sm:gap-4">
                    @if ($code)
                        <div class="flex items-center gap-2">
                            <span class="text-base font-medium text-gray-500 dark:text-gray-400">Discount code:</span>
                            <div class="inline-flex items-center gap-1.5 rounded border border-gray-300 bg-white px-3 py-1.5 dark:border-gray-600 dark:bg-gray-700">
                                <span id="discount-code" class="text-base font-semibold text-gray-900 dark:text-white">{{ $code }}</span>
                                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('discount-code').innerText)" class="text-gray-400 hover:text-gray-900 dark:text-gray-500 dark:hover:text-white">
                                    <x-fwb-o-file-copy class="h-4 w-4" />
                                    <span class="sr-only">Copy code</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 sm:mt-0">
                        @if(isset($actions))
                            {{ $actions }}
                        @else
                            @if ($buttonText)
                                <a href="{{ $buttonHref }}" class="inline-flex items-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                    {{ $buttonText }}
                                    <x-fwb-o-arrow-right class="ms-2 h-5 w-5" />
                                </a>
                            @endif
                        @endif
                    </div>
                </div>

                @if(! $slot->isEmpty())
                    <div class="mt-4">
                        {{ $slot }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
