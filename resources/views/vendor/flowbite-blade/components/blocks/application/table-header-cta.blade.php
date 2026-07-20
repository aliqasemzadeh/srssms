@props([
    'title' => '',
    'description' => '',
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden']) }}>
    @if ($title || $description)
    <div class="flex flex-col p-4 space-y-3 lg:flex-row lg:items-center lg:justify-between lg:space-y-0 lg:space-x-4">
        @if ($title)
        <div class="flex items-center flex-1 space-x-4">
            <h5 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h5>
        </div>
        @endif
        @if(isset($actions))
            {{ $actions }}
        @endif
    </div>
    @if ($description)
    <div class="p-4 pt-0">
        <div class="flex items-center p-4 text-sm text-blue-800 border border-blue-300 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400 dark:border-blue-800" role="alert">
            <x-fwb-o-info-circle class="flex-shrink-0 inline w-4 h-4 mr-3" />
            <span class="sr-only">Info</span>
            <div>
                @if(isset($cta))
                    {{ $cta }}
                @else
                    <p>{{ $description }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif
    @endif
    @unless($slot->isEmpty())
        {{ $slot }}
    @endunless
</div>
