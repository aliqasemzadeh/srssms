@props([
    'title' => '',
    'description' => '',
    'fwbIcon' => '',
    'titleElement' => 'h3',
])

<div>
    @if ($fwbIcon)
        <div class="flex justify-center items-center mb-4 w-10 h-10 rounded-full bg-blue-100 lg:h-12 lg:w-12 dark:bg-blue-900">
            <x-dynamic-component :component="'fwb-' . $fwbIcon" class="w-5 h-5 text-blue-600 lg:w-6 lg:h-6 dark:text-blue-300" />
        </div>
    @endif
    @if ($title)
        <{{ $titleElement }} class="mb-2 text-xl font-bold dark:text-white">{{ $title }}</{{ $titleElement }}>
    @endif
    @if ($slot->isNotEmpty())
        <p class="text-gray-500 dark:text-gray-400">{{ $slot }}</p>
    @elseif ($description)
        <p class="text-gray-500 dark:text-gray-400">{{ $description }}</p>
    @endif
</div>
