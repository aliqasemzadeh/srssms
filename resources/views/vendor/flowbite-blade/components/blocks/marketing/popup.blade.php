@props([
    'title' => 'Privacy info',
    'description' => 'The backup created with this export functance(), will not affect your current data. This backup can be used to restore your data in case of any issues.',
    'id' => 'info-popup',
])

<div {{ $attributes->merge(['class' => 'overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full hidden']) }} id="{{ $id }}">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="{{ $id }}">
                <x-fwb-o-close class="w-5 h-5" />
                <span class="sr-only">Close modal</span>
            </button>
            <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900 p-2 flex items-center justify-center mx-auto mb-3.5">
                <x-fwb-o-info-circle class="w-8 h-8 text-green-500 dark:text-green-400" />
                <span class="sr-only">Info</span>
            </div>
            <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
            <p class="mb-4 text-sm font-normal text-gray-500 dark:text-gray-400">{{ $description }}</p>
            <div class="flex justify-center items-center space-x-4">
                @if(isset($actions))
                    {{ $actions }}
                @else
                    <button data-modal-toggle="{{ $id }}" type="button" class="py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">Cancel</button>
                    <button type="button" class="py-2 px-3 text-sm font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">Confirm</button>
                @endif
            </div>
        </div>
    </div>
</div>