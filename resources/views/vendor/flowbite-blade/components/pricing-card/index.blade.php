<div {{ $attributes->class([$baseClasses()]) }}>
    <h3 class="mb-4 text-2xl font-semibold">{{ $title }}</h3>

    @if ($description)
        <p class="font-light text-body sm:text-lg">{{ $description }}</p>
    @endif

    <div class="flex justify-center items-baseline my-8">
        <span class="mr-2 text-5xl font-extrabold">{{ $currency }}{{ $price }}</span>
        @if ($period)
            <span class="text-body">{{ $period }}</span>
        @endif
    </div>

    <ul role="list" class="mb-8 space-y-4 text-left">
        {{ $slot }}
    </ul>

    @if (isset($footer))
        {{ $footer }}
    @elseif ($buttonText)
        <x-fwb.button :href="$buttonHref" :color="$buttonColor" class="w-full justify-center">{{ $buttonText }}</x-fwb.button>
    @endif
</div>
