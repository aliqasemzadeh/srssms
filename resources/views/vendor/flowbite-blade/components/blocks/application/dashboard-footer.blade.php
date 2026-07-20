@props([
    'brandName' => 'Flowbite',
    'brandHref' => '/',
    'copyright' => '',
])

<footer {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow md:flex md:items-center md:justify-between md:p-6 dark:bg-gray-800 p-4']) }}>
    @if ($copyright)
    <span class="text-sm text-gray-500 sm:text-center dark:text-gray-400">
        {!! $copyright !!}
    </span>
    @endif
    <ul class="flex flex-wrap items-center mt-3 text-sm font-medium text-gray-500 dark:text-gray-400 sm:mt-0">
        @if(isset($links))
            {{ $links }}
        @elseif($slot->isEmpty())
            <li>
                <a href="#" class="hover:underline me-4 md:me-6">About</a>
            </li>
            <li>
                <a href="#" class="hover:underline me-4 md:me-6">Privacy Policy</a>
            </li>
            <li>
                <a href="#" class="hover:underline me-4 md:me-6">Licensing</a>
            </li>
            <li>
                <a href="#" class="hover:underline">Contact</a>
            </li>
        @else
            {{ $slot }}
        @endif
    </ul>
</footer>
