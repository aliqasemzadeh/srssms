@props([
    'brandName' => 'Flowbite',
    'copyright' => '&copy; ' . date('Y') . ' Flowbite&trade;. All Rights Reserved.',
])

<footer {{ $attributes->merge(['class' => 'p-4 bg-white md:p-8 lg:p-10 dark:bg-gray-800']) }}>
    <div class="mx-auto max-w-screen-xl text-center">
        <a href="#" class="flex justify-center items-center text-2xl font-semibold text-gray-900 dark:text-white">
            @if(isset($logo))
                {{ $logo }}
            @else
                <svg class="mr-2 h-8" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M25.2696 13.126C25.1955 13.6364 24.8589 14.3299 24.4728 14.9328C23.9856 15.6936 23.2125 16.2264 22.3276 16.4114L18.43 17.2265C17.8035 17.3575 17.2899 17.8139 17.0918 18.4189L15.8089 22.2194C15.5528 23.0001 15.0349 23.6769 14.2728 24.0484C13.3171 24.5128 12.146 24.2945 11.4136 23.5765L8.04929 20.2862C7.55599 19.8038 6.88498 19.5421 6.18974 19.5595L4.00072 19.6134C3.01099 19.6387 2.09459 19.1826 1.5765 18.3894C1.0584 17.5963 1.01985 16.5882 1.47397 15.759L3.27751 12.3758C3.60352 11.7643 3.69856 11.052 3.54202 10.3731L2.86186 7.42969C2.64698 6.49972 2.95438 5.52913 3.6737 4.89469C4.39303 4.26024 5.38866 4.07426 6.28836 4.4023L8.87994 5.34554C9.52424 5.58014 10.2328 5.5472 10.8528 5.25401L13.6812 3.91493C14.5246 3.51572 15.5183 3.56003 16.3208 4.03367C17.1234 4.50731 17.6222 5.34498 17.6645 6.26748L17.7634 8.37983C17.7942 9.0337 18.0942 9.64704 18.5922 10.0723L20.1379 11.3909C20.5765 11.7655 21.1241 11.9868 21.6962 12.0218L24.1765 12.1738C24.7289 12.2074 25.1853 12.5915 25.2696 13.126Z" fill="#1C64F2"/>
                    <path d="M25.2696 13.126C25.1955 13.6364 24.8589 14.3299 24.4728 14.9328C23.9856 15.6936 23.2125 16.2264 22.3276 16.4114L18.43 17.2265C17.8035 17.3575 17.2899 17.8139 17.0918 18.4189L15.8089 22.2194C15.5528 23.0001 15.0349 23.6769 14.2728 24.0484C13.3171 24.5128 12.146 24.2945 11.4136 23.5765L8.04929 20.2862C7.55599 19.8038 6.88498 19.5421 6.18974 19.5595L4.00072 19.6134C3.01099 19.6387 2.09459 19.1826 1.5765 18.3894C1.0584 17.5963 1.01985 16.5882 1.47397 15.759L3.27751 12.3758C3.60352 11.7643 3.69856 11.052 3.54202 10.3731L2.86186 7.42969C2.64698 6.49972 2.95438 5.52913 3.6737 4.89469C4.39303 4.26024 5.38866 4.07426 6.28836 4.4023L8.87994 5.34554C9.52424 5.58014 10.2328 5.5472 10.8528 5.25401L13.6812 3.91493C14.5246 3.51572 15.5183 3.56003 16.3208 4.03367C17.1234 4.50731 17.6222 5.34498 17.6645 6.26748L17.7634 8.37983C17.7942 9.0337 18.0942 9.64704 18.5922 10.0723L20.1379 11.3909C20.5765 11.7655 21.1241 11.9868 21.6962 12.0218L24.1765 12.1738C24.7289 12.2074 25.1853 12.5915 25.2696 13.126Z" fill="url(#paint0_linear)" fill-opacity="0.3"/>
                    <defs>
                        <linearGradient id="paint0_linear" x1="1" y1="1" x2="25" y2="25" gradientUnits="userSpaceOnUse">
                            <stop stop-color="white"/>
                            <stop offset="1" stop-color="white" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                </svg>
                {{ $brandName }}
            @endif
        </a>
        <p class="my-6 text-gray-500 dark:text-gray-400">Open-source library of over 400+ web components and interactive elements built for best practices.</p>
        <ul class="flex flex-wrap justify-center items-center mb-6 text-gray-900 dark:text-white">
            @if(isset($links))
                {{ $links }}
            @else
                <li><a href="#" class="mr-4 hover:underline md:mr-6">About</a></li>
                <li><a href="#" class="mr-4 hover:underline md:mr-6">Premium</a></li>
                <li><a href="#" class="mr-4 hover:underline md:mr-6">Campaigns</a></li>
                <li><a href="#" class="mr-4 hover:underline md:mr-6">Blog</a></li>
                <li><a href="#" class="mr-4 hover:underline md:mr-6">Affiliate Program</a></li>
                <li><a href="#" class="mr-4 hover:underline md:mr-6">FAQs</a></li>
                <li><a href="#" class="mr-4 hover:underline md:mr-6">Contact</a></li>
            @endif
        </ul>
        <span class="text-sm text-gray-500 sm:text-center dark:text-gray-400">{!! $copyright !!}</span>
    </div>
</footer>