@props([
    'brandName' => 'Flowbite',
    'brandHref' => '/',
    'id' => 'default-sidebar',
])

<aside {{ $attributes->merge(['class' => 'fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0']) }} id="{{ $id }}" aria-label="Sidebar">
    <div class="h-full px-3 py-4 overflow-y-auto bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
        <a href="{{ $brandHref }}" class="flex items-center ps-2.5 mb-5">
            @if(isset($logo))
                {{ $logo }}
            @else
                <svg class="h-6 me-3 sm:h-7" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M25.2696 13.126C25.1955 13.6364 24.8589 14.3299 24.4728 14.9328C23.9856 15.6936 23.2125 16.2264 22.3276 16.4114L18.43 17.2265C17.8035 17.3575 17.2899 17.8139 17.0918 18.4189L15.8089 22.2194C15.5528 23.0001 15.0349 23.6769 14.2728 24.0484C13.3171 24.5128 12.146 24.2945 11.4136 23.5765L8.04929 20.2862C7.55599 19.8038 6.88498 19.5421 6.18974 19.5595L4.00072 19.6134C3.01099 19.6387 2.09459 19.1826 1.5765 18.3894C1.0584 17.5963 1.01985 16.5882 1.47397 15.759L3.27751 12.3758C3.60352 11.7643 3.69856 11.052 3.54202 10.3731L2.86186 7.42969C2.64698 6.49972 2.95438 5.52913 3.6737 4.89469C4.39303 4.26024 5.38866 4.07426 6.28836 4.4023L8.87994 5.34554C9.52424 5.58014 10.2328 5.5472 10.8528 5.25401L13.6812 3.91493C14.5246 3.51572 15.5183 3.56003 16.3208 4.03367C17.1234 4.50731 17.6222 5.34498 17.6645 6.26748L17.7634 8.37983C17.7942 9.0337 18.0942 9.64704 18.5922 10.0723L20.1379 11.3909C20.5765 11.7655 21.1241 11.9868 21.6962 12.0218L24.1765 12.1738C24.7289 12.2074 25.1853 12.5915 25.2696 13.126Z" fill="#1C64F2"/>
                </svg>
            @endif
            <span class="self-center text-xl font-semibold whitespace-nowrap dark:text-white">{{ $brandName }}</span>
        </a>

        @if($slot->isEmpty())
            <ul class="space-y-2 font-medium">
                <li>
                    <a href="#" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                        <x-fwb-o-chart-pie class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                        <span class="ms-3">Dashboard</span>
                    </a>
                </li>
                <li>
                    <button type="button" class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700" aria-controls="dropdown-ecommerce" data-collapse-toggle="dropdown-ecommerce">
                        <x-fwb-o-cart class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" />
                        <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">E-Commerce</span>
                        <x-fwb-o-chevron-down class="w-3 h-3" />
                    </button>
                    <ul id="dropdown-ecommerce" class="hidden py-2 space-y-2">
                        <li>
                            <a href="#" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Products</a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Billing</a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Invoice</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                        <x-fwb-o-users class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                        <span class="flex-1 ms-3 whitespace-nowrap">Users</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                        <x-fwb-o-shopping-bag class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                        <span class="flex-1 ms-3 whitespace-nowrap">Products</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                        <x-fwb-o-arrow-right-to-bracket class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                        <span class="flex-1 ms-3 whitespace-nowrap">Sign In</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                        <x-fwb-o-pen class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                        <span class="flex-1 ms-3 whitespace-nowrap">Sign Up</span>
                    </a>
                </li>
            </ul>
        @else
            {{ $slot }}
        @endif
    </div>
</aside>
