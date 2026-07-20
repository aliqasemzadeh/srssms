@props([
    'brandName' => 'Flowbite',
    'brandHref' => '/',
    'brandLogoSrc' => 'https://flowbite.s3.amazonaws.com/logo.svg',
    'cartCount' => 0,
    'cartHref' => '#',
    'searchAction' => '#',
])

<header {{ $attributes->merge(['class' => 'antialiased']) }}>
    <nav class="border-gray-200 bg-white px-4 py-2.5 dark:bg-gray-800 lg:px-6">
        <div class="flex flex-wrap items-center justify-between">
            <div class="flex items-center justify-start">
                {{-- Mobile menu toggle --}}
                <button type="button" data-drawer-target="ecommerce-navbar-menu" data-drawer-toggle="ecommerce-navbar-menu" class="mr-2 rounded-lg p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:bg-gray-100 focus:ring-2 focus:ring-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:bg-gray-700 dark:focus:ring-gray-700 lg:hidden">
                    <x-fwb-o-bars class="h-[18px] w-[18px]" />
                    <span class="sr-only">Toggle sidebar</span>
                </button>

                {{-- Brand --}}
                <a href="{{ $brandHref }}" class="mr-4 flex">
                    <img src="{{ $brandLogoSrc }}" class="mr-3 h-8" alt="{{ $brandName }} Logo" />
                    <span class="self-center whitespace-nowrap text-2xl font-semibold dark:text-white">{{ $brandName }}</span>
                </a>

                {{-- Search form --}}
                <form action="{{ $searchAction }}" method="GET" class="hidden lg:block lg:pl-2">
                    <label for="topbar-search" class="sr-only">Search</label>
                    <div class="relative mt-1 lg:w-96">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-fwb-o-search class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                        </div>
                        <input type="text" name="q" id="topbar-search" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 pl-9 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500 sm:text-sm" placeholder="Search" />
                    </div>
                </form>
            </div>

            <div class="flex items-center lg:order-2">
                @if(isset($nav))
                    {{ $nav }}
                @endif

                {{-- Mobile search button --}}
                <button type="button" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white lg:hidden">
                    <span class="sr-only">Search</span>
                    <x-fwb-o-search class="h-4 w-4" />
                </button>

                {{-- Cart button --}}
                <a href="{{ $cartHref }}" class="relative mr-1 rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus:ring-4 focus:ring-gray-300 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-600">
                    <span class="sr-only">View cart</span>
                    <x-fwb-o-cart class="h-5 w-5" />
                    @if($cartCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-600 text-[10px] font-bold text-white">{{ $cartCount }}</span>
                    @endif
                </a>

                @if(isset($actions))
                    {{ $actions }}
                @else
                    {{-- User menu button --}}
                    <button type="button" class="mx-3 flex rounded-full bg-gray-800 text-sm focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600 md:mr-0" id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown">
                        <span class="sr-only">Open user menu</span>
                        <x-fwb-s-user-circle class="h-8 w-8 rounded-full text-gray-400" />
                    </button>
                @endif
            </div>
        </div>
    </nav>

    @if($slot->isEmpty())
        {{-- Default secondary navigation --}}
        <nav class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">
            <div class="mx-auto max-w-screen-xl px-4">
                <div class="flex items-center overflow-x-auto py-3">
                    <ul class="flex flex-nowrap items-center gap-4 text-sm font-medium text-gray-500 dark:text-gray-400">
                        <li><a href="#" class="whitespace-nowrap hover:text-blue-600 dark:hover:text-blue-500">Home</a></li>
                        <li><a href="#" class="whitespace-nowrap hover:text-blue-600 dark:hover:text-blue-500">Best Sellers</a></li>
                        <li><a href="#" class="whitespace-nowrap hover:text-blue-600 dark:hover:text-blue-500">Gift Ideas</a></li>
                        <li><a href="#" class="whitespace-nowrap hover:text-blue-600 dark:hover:text-blue-500">Games</a></li>
                        <li><a href="#" class="whitespace-nowrap hover:text-blue-600 dark:hover:text-blue-500">Electronics</a></li>
                        <li><a href="#" class="whitespace-nowrap hover:text-blue-600 dark:hover:text-blue-500">Home & Garden</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    @else
        {{ $slot }}
    @endif
</header>
