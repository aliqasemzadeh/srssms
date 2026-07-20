@props([
    'title' => '',
    'seeMoreHref' => '#',
    'seeMoreText' => '',
])

<section {{ $attributes->merge(['class' => 'bg-gray-50 py-8 antialiased dark:bg-gray-900 md:py-16']) }}>
    <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
        @if ($title || $seeMoreText)
            <div class="mb-4 flex items-center justify-between gap-4 md:mb-8">
                @if ($title)
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">{{ $title }}</h2>
                @endif

                @if ($seeMoreText)
                    <a href="{{ $seeMoreHref }}" title="" class="flex items-center text-base font-medium text-blue-700 hover:underline dark:text-blue-500">
                        {{ $seeMoreText }}
                        <x-fwb-o-arrow-right class="ms-1 h-5 w-5" />
                    </a>
                @endif
            </div>
        @endif

        @if($slot->isEmpty())
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                <a href="#" class="flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                    <x-fwb-o-desktop-pc class="me-2 h-4 w-4 shrink-0 text-gray-900 dark:text-white" />
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Computer & Office</span>
                </a>

                <a href="#" class="flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                    <x-fwb-o-phone class="me-2 h-4 w-4 shrink-0 text-gray-900 dark:text-white" />
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Phone & Tablet</span>
                </a>

                <a href="#" class="flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                    <x-fwb-o-shopping-bag class="me-2 h-4 w-4 shrink-0 text-gray-900 dark:text-white" />
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Fashion</span>
                </a>

                <a href="#" class="flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                    <x-fwb-o-grid class="me-2 h-4 w-4 shrink-0 text-gray-900 dark:text-white" />
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Gaming</span>
                </a>

                <a href="#" class="flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                    <x-fwb-o-home class="me-2 h-4 w-4 shrink-0 text-gray-900 dark:text-white" />
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Home & Garden</span>
                </a>

                <a href="#" class="flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                    <x-fwb-o-clock class="me-2 h-4 w-4 shrink-0 text-gray-900 dark:text-white" />
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Watches & Jewelry</span>
                </a>

                <a href="#" class="flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                    <x-fwb-o-book class="me-2 h-4 w-4 shrink-0 text-gray-900 dark:text-white" />
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Books & Media</span>
                </a>

                <a href="#" class="flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                    <x-fwb-o-headphones class="me-2 h-4 w-4 shrink-0 text-gray-900 dark:text-white" />
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Health & Beauty</span>
                </a>
            </div>
        @else
            {{ $slot }}
        @endif
    </div>
</section>
