@props([
    'title' => '',
    'id' => 'filterDropdown',
    'action' => '#',
    'submitText' => '',
    'resetText' => '',
])

<div {{ $attributes->merge(['class' => '']) }} id="{{ $id }}">
    <div class="w-full max-w-sm bg-white rounded-lg shadow dark:bg-gray-700 p-4">
        @if ($title || $resetText)
        <div class="flex items-center justify-between mb-4">
            @if ($title)
            <h6 class="text-sm font-medium text-gray-900 dark:text-white">{{ $title }}</h6>
            @endif
            @if(isset($headerActions))
                {{ $headerActions }}
            @elseif ($resetText)
                <a href="{{ $action }}" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-500">{{ $resetText }}</a>
            @endif
        </div>
        @endif

        @if($slot->isEmpty())
            {{-- Default filter groups --}}
            <div class="space-y-4">
                {{-- Category Filter --}}
                <div>
                    <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">Category</h6>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center">
                            <input id="apple" type="checkbox" value="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="apple" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Apple (56)</label>
                        </li>
                        <li class="flex items-center">
                            <input id="fitbit" type="checkbox" value="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="fitbit" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Fitbit (56)</label>
                        </li>
                        <li class="flex items-center">
                            <input id="dell" type="checkbox" value="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="dell" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Dell (56)</label>
                        </li>
                        <li class="flex items-center">
                            <input id="asus" type="checkbox" value="" checked class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="asus" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Asus (97)</label>
                        </li>
                    </ul>
                </div>

                {{-- Price Filter --}}
                <div>
                    <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">Price</h6>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center">
                            <input id="under-500" type="radio" value="" name="price" class="w-4 h-4 bg-gray-100 border-gray-300 text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="under-500" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Under $500</label>
                        </li>
                        <li class="flex items-center">
                            <input id="500-1000" type="radio" value="" name="price" class="w-4 h-4 bg-gray-100 border-gray-300 text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="500-1000" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">$500 to $1,000</label>
                        </li>
                        <li class="flex items-center">
                            <input id="over-1000" type="radio" value="" name="price" class="w-4 h-4 bg-gray-100 border-gray-300 text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="over-1000" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Over $1,000</label>
                        </li>
                    </ul>
                </div>
            </div>

            @if ($submitText)
            <div class="mt-4">
                <button type="button" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">{{ $submitText }}</button>
            </div>
            @endif
        @else
            {{ $slot }}
        @endif
    </div>
</div>
