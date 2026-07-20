@props([
    'title' => '',
])

<section {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900']) }}>
    <div class="py-8 px-4 mx-auto max-w-2xl lg:py-16">
        @if ($title)
        <h2 class="mb-2 text-xl font-semibold leading-none text-gray-900 md:text-2xl dark:text-white">{{ $title }}</h2>
        @endif
        @if($slot->isEmpty())
            <dl>
                <dt class="mb-2 font-semibold leading-none text-gray-900 dark:text-white">Name</dt>
                <dd class="mb-4 font-light text-gray-500 sm:mb-5 dark:text-gray-400">Apple iMac 27"</dd>
                <dt class="mb-2 font-semibold leading-none text-gray-900 dark:text-white">Category</dt>
                <dd class="mb-4 font-light text-gray-500 sm:mb-5 dark:text-gray-400">
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium inline-flex items-center px-2.5 py-0.5 rounded dark:bg-blue-200 dark:text-blue-800">
                        <x-fwb-s-video class="mr-1 w-3 h-3" />
                        Electronics
                    </span>
                </dd>
                <dt class="mb-2 font-semibold leading-none text-gray-900 dark:text-white">Brand / Manufacturer</dt>
                <dd class="mb-4 font-light text-gray-500 sm:mb-5 dark:text-gray-400">Apple</dd>
                <dt class="mb-2 font-semibold leading-none text-gray-900 dark:text-white">Item weight (net)</dt>
                <dd class="mb-4 font-light text-gray-500 sm:mb-5 dark:text-gray-400">12kg</dd>
                <dt class="mb-2 font-semibold leading-none text-gray-900 dark:text-white">Description</dt>
                <dd class="mb-4 font-light text-gray-500 sm:mb-5 dark:text-gray-400">Standard glass, 3.8GHz 8-core 10th-generation Intel Core i7 processor, Turbo Boost up to 5.0GHz, 16GB 2666MHz DDR4 memory, Radeon Pro 5500 XT with 8GB of GDDR6 memory, 256GB SSD storage, Gigabit Ethernet, Two Thunderbolt 3 ports.</dd>
                <dt class="mb-2 font-semibold leading-none text-gray-900 dark:text-white">Colors</dt>
                <dd class="flex items-center space-x-2 font-light text-gray-500 sm:mb-5 dark:text-gray-400">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-300"></div>
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-900"></div>
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600"></div>
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-red-500"></div>
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-400"></div>
                </dd>
            </dl>
            <dl class="flex items-center space-x-6">
                <div>
                    <dt class="mb-2 font-semibold leading-none text-gray-900 dark:text-white">Price</dt>
                    <dd class="mb-4 font-light text-gray-500 sm:mb-5 dark:text-gray-400">$2999</dd>
                </div>
                <div>
                    <dt class="mb-2 font-semibold leading-none text-gray-900 dark:text-white">Discount</dt>
                    <dd class="mb-4 font-light text-gray-500 sm:mb-5 dark:text-gray-400">
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium inline-flex items-center px-2.5 py-0.5 rounded dark:bg-blue-200 dark:text-blue-800">
                            20% off
                        </span>
                    </dd>
                </div>
            </dl>
            <div class="flex items-center space-x-4">
                <button type="button" class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    <x-fwb-o-pen class="mr-1 -ml-1 w-5 h-5" />
                    Edit
                </button>
                <button type="button" class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                    Preview
                </button>
                <button type="button" class="inline-flex items-center text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900">
                    <x-fwb-o-trash-bin class="w-5 h-5 mr-1.5 -ml-1" />
                    Delete
                </button>
            </div>
        @else
            {{ $slot }}
        @endif
    </div>
</section>
