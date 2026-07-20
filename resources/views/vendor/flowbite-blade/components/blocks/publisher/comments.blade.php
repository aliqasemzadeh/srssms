@props([
    'title' => '',
    'commentCount' => '',
])

<section {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900 py-8 lg:py-16 antialiased']) }}>
    <div class="max-w-2xl mx-auto px-4">
        @if ($title || $commentCount)
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg lg:text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}@if ($commentCount) ({{ $commentCount }})@endif</h2>
            </div>
        @endif

        @if(isset($form))
            {{ $form }}
        @endif

        {{ $slot }}
    </div>
</section>
