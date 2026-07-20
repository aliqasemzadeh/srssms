@props([
    'title' => '',
    'id' => 'filter-modal',
])

<div id="{{ $id }}" tabindex="-1" aria-hidden="true" {{ $attributes->merge(['class' => 'hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full']) }}>
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">
            {{-- Modal header --}}
            @if ($title)
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                <button type="button" data-modal-hide="{{ $id }}" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                    <x-fwb-o-close class="w-3 h-3" />
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            @endif

            {{-- Modal body --}}
            <div class="p-4 md:p-5">
                {{ $slot }}
            </div>

            {{-- Modal footer --}}
            @if(isset($footer))
            <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                {{ $footer }}
            </div>
            @else
            <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                <button type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Show results</button>
                <button type="button" data-modal-hide="{{ $id }}" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Reset</button>
            </div>
            @endif
        </div>
    </div>
</div>

@once
@pushOnce('default-slot')
{{-- Default slot content for faceted-search-modal --}}
<div class="space-y-4">
    <h4 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">Category</h4>
    <div class="flex items-center">
        <input id="filter-category-1" type="checkbox" value="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
        <label for="filter-category-1" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Laptops</label>
    </div>
    <div class="flex items-center">
        <input id="filter-category-2" type="checkbox" value="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
        <label for="filter-category-2" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Phones</label>
    </div>
    <div class="flex items-center">
        <input id="filter-category-3" type="checkbox" value="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
        <label for="filter-category-3" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Tablets</label>
    </div>

    <h4 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">Price range</h4>
    <div class="flex items-center space-x-4">
        <div class="w-full">
            <label for="filter-price-min" class="sr-only">Min price</label>
            <input type="number" id="filter-price-min" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Min">
        </div>
        <div class="w-full">
            <label for="filter-price-max" class="sr-only">Max price</label>
            <input type="number" id="filter-price-max" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Max">
        </div>
    </div>

    <h4 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">Rating</h4>
    <div class="flex items-center">
        <input id="filter-rating-5" type="radio" name="rating" value="5" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
        <label for="filter-rating-5" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">5 stars</label>
    </div>
    <div class="flex items-center">
        <input id="filter-rating-4" type="radio" name="rating" value="4" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
        <label for="filter-rating-4" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">4 stars & up</label>
    </div>
</div>
@endPushOnce
@endonce
