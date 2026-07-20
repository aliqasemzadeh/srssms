@props([
    'title' => '',
    'buttonText' => '',
    'action' => '#',
    'searchPlaceholder' => '',
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden']) }}>
    <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
        @if ($title)
        <div class="w-full md:w-1/2">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
        </div>
        @endif
        <div class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
            @if(isset($search))
                {{ $search }}
            @else
                <form class="flex items-center">
                    <label for="simple-search" class="sr-only">Search</label>
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <x-fwb-o-search class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        </div>
                        <input type="text" id="simple-search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="{{ $searchPlaceholder }}">
                    </div>
                </form>
            @endif
            @if(isset($actions))
                {{ $actions }}
            @else
                @if ($buttonText)
                <a href="{{ $action }}" class="flex items-center justify-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                    <x-fwb-o-plus class="h-3.5 w-3.5 mr-2" />
                    {{ $buttonText }}
                </a>
                @endif
            @endif
        </div>
    </div>
    @unless($slot->isEmpty())
        {{ $slot }}
    @endunless
</div>
