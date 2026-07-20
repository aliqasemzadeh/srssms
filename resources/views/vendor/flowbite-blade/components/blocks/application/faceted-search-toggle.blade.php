@props([
    'title' => '',
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900']) }}>
    <div class="py-4 px-4 mx-auto max-w-screen-xl lg:px-6">
        @if ($title)
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
            <button type="button" data-collapse-toggle="filter-toggle-panel" class="inline-flex items-center text-sm font-medium text-blue-700 hover:underline dark:text-blue-500">
                <x-fwb-o-adjustments-horizontal class="w-4 h-4 mr-1" />
                Toggle filters
            </button>
        </div>
        @endif

        <div id="filter-toggle-panel" class="hidden">
            {{ $slot }}
        </div>

        @if(isset($results))
        <div class="mt-4">
            {{ $results }}
        </div>
        @endif
    </div>
</div>

@once
@pushOnce('default-slot')
{{-- Default slot content for faceted-search-toggle --}}
<div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 pb-4 border-b border-gray-200 dark:border-gray-700">
    <div>
        <h3 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">Category</h3>
        <div class="space-y-2">
            <label class="flex items-center justify-between cursor-pointer">
                <span class="text-sm font-medium text-gray-900 dark:text-gray-300">Laptops</span>
                <div class="relative">
                    <input type="checkbox" value="" class="sr-only peer">
                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                </div>
            </label>
            <label class="flex items-center justify-between cursor-pointer">
                <span class="text-sm font-medium text-gray-900 dark:text-gray-300">Phones</span>
                <div class="relative">
                    <input type="checkbox" value="" class="sr-only peer">
                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                </div>
            </label>
            <label class="flex items-center justify-between cursor-pointer">
                <span class="text-sm font-medium text-gray-900 dark:text-gray-300">Tablets</span>
                <div class="relative">
                    <input type="checkbox" value="" class="sr-only peer">
                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                </div>
            </label>
        </div>
    </div>
    <div>
        <h3 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">Brand</h3>
        <div class="space-y-2">
            <label class="flex items-center justify-between cursor-pointer">
                <span class="text-sm font-medium text-gray-900 dark:text-gray-300">Apple</span>
                <div class="relative">
                    <input type="checkbox" value="" class="sr-only peer">
                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                </div>
            </label>
            <label class="flex items-center justify-between cursor-pointer">
                <span class="text-sm font-medium text-gray-900 dark:text-gray-300">Samsung</span>
                <div class="relative">
                    <input type="checkbox" value="" class="sr-only peer">
                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                </div>
            </label>
        </div>
    </div>
    <div>
        <h3 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">Price</h3>
        <div class="flex items-center space-x-4">
            <div class="w-full">
                <input type="number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Min">
            </div>
            <div class="w-full">
                <input type="number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Max">
            </div>
        </div>
    </div>
    <div class="flex items-end">
        <button type="button" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Apply filters</button>
    </div>
</div>
@endPushOnce
@endonce
