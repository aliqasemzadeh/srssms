@props([
    'showing' => '',
    'total' => '',
])

<nav {{ $attributes->merge(['class' => 'flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 p-4']) }} aria-label="Table navigation">
    @if ($showing || $total)
    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
        Showing
        <span class="font-semibold text-gray-900 dark:text-white">{{ $showing }}</span>
        of
        <span class="font-semibold text-gray-900 dark:text-white">{{ $total }}</span>
    </span>
    @endif
    @if(isset($buttons))
        {{ $buttons }}
    @else
        <div class="inline-flex items-stretch -space-x-px">
            <a href="#" class="flex items-center justify-center py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-l-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                <x-fwb-o-chevron-left class="w-5 h-5 mr-2" />
                Previous
            </a>
            <a href="#" class="flex items-center justify-center py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-r-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                Next
                <x-fwb-o-chevron-right class="w-5 h-5 ml-2" />
            </a>
        </div>
    @endif
</nav>
