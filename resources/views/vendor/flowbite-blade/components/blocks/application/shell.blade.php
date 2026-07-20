@props([
    'brandName' => 'Flowbite',
    'brandHref' => '/',
    'sidebarId' => 'logo-sidebar',
])

<div {{ $attributes->merge(['class' => 'antialiased bg-gray-50 dark:bg-gray-900']) }}>
    {{-- Navbar --}}
    @if(isset($navbar))
        {{ $navbar }}
    @else
        <nav class="bg-white border-b border-gray-200 px-4 py-2.5 dark:bg-gray-800 dark:border-gray-700 fixed left-0 right-0 top-0 z-50">
            <div class="flex flex-wrap justify-between items-center">
                <div class="flex justify-start items-center">
                    <button data-drawer-target="{{ $sidebarId }}" data-drawer-toggle="{{ $sidebarId }}" aria-controls="{{ $sidebarId }}" class="p-2 mr-2 text-gray-600 rounded-lg cursor-pointer md:hidden hover:text-gray-900 hover:bg-gray-100 focus:bg-gray-100 dark:focus:bg-gray-700 focus:ring-2 focus:ring-gray-100 dark:focus:ring-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                        <x-fwb-o-bars class="w-6 h-6" />
                        <x-fwb-o-close class="hidden w-6 h-6" />
                        <span class="sr-only">Toggle sidebar</span>
                    </button>
                    <a href="{{ $brandHref }}" class="flex items-center justify-between mr-4">
                        @if(isset($logo))
                            {{ $logo }}
                        @else
                            <svg class="mr-3 h-8" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M25.2696 13.126C25.1955 13.6364 24.8589 14.3299 24.4728 14.9328C23.9856 15.6936 23.2125 16.2264 22.3276 16.4114L18.43 17.2265C17.8035 17.3575 17.2899 17.8139 17.0918 18.4189L15.8089 22.2194C15.5528 23.0001 15.0349 23.6769 14.2728 24.0484C13.3171 24.5128 12.146 24.2945 11.4136 23.5765L8.04929 20.2862C7.55599 19.8038 6.88498 19.5421 6.18974 19.5595L4.00072 19.6134C3.01099 19.6387 2.09459 19.1826 1.5765 18.3894C1.0584 17.5963 1.01985 16.5882 1.47397 15.759L3.27751 12.3758C3.60352 11.7643 3.69856 11.052 3.54202 10.3731L2.86186 7.42969C2.64698 6.49972 2.95438 5.52913 3.6737 4.89469C4.39303 4.26024 5.38866 4.07426 6.28836 4.4023L8.87994 5.34554C9.52424 5.58014 10.2328 5.5472 10.8528 5.25401L13.6812 3.91493C14.5246 3.51572 15.5183 3.56003 16.3208 4.03367C17.1234 4.50731 17.6222 5.34498 17.6645 6.26748L17.7634 8.37983C17.7942 9.0337 18.0942 9.64704 18.5922 10.0723L20.1379 11.3909C20.5765 11.7655 21.1241 11.9868 21.6962 12.0218L24.1765 12.1738C24.7289 12.2074 25.1853 12.5915 25.2696 13.126Z" fill="#1C64F2"/>
                            </svg>
                        @endif
                        <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">{{ $brandName }}</span>
                    </a>
                </div>
                @if(isset($navbarEnd))
                    {{ $navbarEnd }}
                @endif
            </div>
        </nav>
    @endif

    {{-- Sidebar --}}
    <aside id="{{ $sidebarId }}" class="fixed top-0 left-0 z-40 w-64 h-screen pt-14 transition-transform -translate-x-full bg-white border-r border-gray-200 md:translate-x-0 dark:bg-gray-800 dark:border-gray-700" aria-label="Sidenav">
        <div class="overflow-y-auto py-5 px-3 h-full bg-white dark:bg-gray-800">
            @if(isset($sidebar))
                {{ $sidebar }}
            @else
                <ul class="space-y-2">
                    <li>
                        <a href="#" class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <x-fwb-o-chart-pie class="w-6 h-6 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                            <span class="ml-3">Overview</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <x-fwb-o-file class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                            <span class="flex-1 ml-3 whitespace-nowrap">Pages</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <x-fwb-o-shopping-bag class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                            <span class="flex-1 ml-3 whitespace-nowrap">Sales</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <x-fwb-o-inbox class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                            <span class="flex-1 ml-3 whitespace-nowrap">Messages</span>
                        </a>
                    </li>
                </ul>
            @endif
        </div>
        @if(isset($sidebarBottom))
            <div class="hidden absolute bottom-0 left-0 justify-center p-4 space-x-4 w-full lg:flex bg-white dark:bg-gray-800 z-20 border-r border-gray-200 dark:border-gray-700">
                {{ $sidebarBottom }}
            </div>
        @endif
    </aside>

    {{-- Main Content --}}
    <main class="p-4 md:ml-64 h-auto pt-20">
        @if(isset($content))
            {{ $content }}
        @else
            {{ $slot }}
        @endif
    </main>
</div>
