@props([
    'title' => '',
    'id' => 'filter-drawer',
])

<div id="{{ $id }}" {{ $attributes->merge(['class' => 'fixed top-0 left-0 z-40 w-64 h-screen p-4 overflow-y-auto transition-transform -translate-x-full bg-white dark:bg-gray-800']) }} tabindex="-1" aria-labelledby="{{ $id }}-label">
    <div class="flex items-center justify-between mb-4">
        @if ($title)
        <h5 id="{{ $id }}-label" class="text-base font-semibold text-gray-500 uppercase dark:text-gray-400">{{ $title }}</h5>
        @endif
        <button type="button" data-drawer-hide="{{ $id }}" aria-controls="{{ $id }}" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white">
            <x-fwb-o-close class="w-3 h-3" />
            <span class="sr-only">Close drawer</span>
        </button>
    </div>

    <div class="py-4 overflow-y-auto">
        {{ $slot }}
    </div>

    @if(isset($footer))
    <div class="flex items-center space-x-4 mt-4 border-t border-gray-200 pt-4 dark:border-gray-600">
        {{ $footer }}
    </div>
    @else
    <div class="flex items-center space-x-4 mt-4 border-t border-gray-200 pt-4 dark:border-gray-600">
        <button type="button" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Apply filters</button>
        <button type="button" data-drawer-hide="{{ $id }}" class="w-full py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Reset</button>
    </div>
    @endif
</div>

@once
@pushOnce('default-slot')
{{-- Default slot content for faceted-search-drawer --}}
<div class="space-y-6">
    <div>
        <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">Category</h6>
        <ul class="space-y-2">
            <li class="flex items-center">
                <input id="drawer-filter-cat-1" type="checkbox" value="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                <label for="drawer-filter-cat-1" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Laptops</label>
            </li>
            <li class="flex items-center">
                <input id="drawer-filter-cat-2" type="checkbox" value="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                <label for="drawer-filter-cat-2" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Phones</label>
            </li>
            <li class="flex items-center">
                <input id="drawer-filter-cat-3" type="checkbox" value="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                <label for="drawer-filter-cat-3" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Tablets</label>
            </li>
        </ul>
    </div>

    <div>
        <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">Price range</h6>
        <div class="space-y-3">
            <div class="flex items-center space-x-4">
                <div class="w-full">
                    <label for="drawer-filter-min" class="sr-only">Min price</label>
                    <input type="number" id="drawer-filter-min" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Min">
                </div>
                <span class="text-sm text-gray-500 dark:text-gray-400">to</span>
                <div class="w-full">
                    <label for="drawer-filter-max" class="sr-only">Max price</label>
                    <input type="number" id="drawer-filter-max" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Max">
                </div>
            </div>
        </div>
    </div>

    <div>
        <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">Rating</h6>
        <ul class="space-y-2">
            <li class="flex items-center">
                <input id="drawer-filter-rating-5" type="radio" name="drawer-rating" value="5" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                <label for="drawer-filter-rating-5" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">5 stars</label>
            </li>
            <li class="flex items-center">
                <input id="drawer-filter-rating-4" type="radio" name="drawer-rating" value="4" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                <label for="drawer-filter-rating-4" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">4 stars & up</label>
            </li>
            <li class="flex items-center">
                <input id="drawer-filter-rating-3" type="radio" name="drawer-rating" value="3" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                <label for="drawer-filter-rating-3" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">3 stars & up</label>
            </li>
        </ul>
    </div>
</div>
@endPushOnce
@endonce
