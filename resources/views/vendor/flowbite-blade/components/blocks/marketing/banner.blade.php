@props([
    'message' => 'Get 5% commission per sale',
    'href' => '#',
    'id' => 'banner',
])

<div id="{{ $id }}" tabindex="-1" {{ $attributes->merge(['class' => 'flex fixed z-50 gap-8 justify-between items-start py-3 px-4 w-full bg-gray-50 border border-b border-gray-200 sm:items-center dark:border-gray-700 lg:py-4 dark:bg-gray-800']) }}>
    <p class="text-sm font-light text-gray-500 dark:text-gray-400">
        {{ $slot->isNotEmpty() ? $slot : '' }}
        @if($slot->isEmpty())
        <span class="font-normal text-gray-900 dark:text-white">{{ $message }}</span>
        <a class="flex items-center text-sm font-medium text-blue-600 md:ms-1 md:inline-flex dark:text-blue-500 hover:underline" href="{{ $href }}">
            Learn More
            <x-fwb-o-arrow-right class="ml-1 w-4 h-4" />
        </a>
        @endif
    </p>
    <button data-collapse-toggle="{{ $id }}" type="button" class="flex items-center text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
        <x-fwb-o-close class="w-5 h-5" />
    </button>
</div>
