@props([
    'title' => 'Sign in to your account',
    'action' => '#',
    'brandName' => 'Flowbite',
])

<section {{ $attributes->merge(['class' => 'bg-gray-50 dark:bg-gray-900']) }}>
    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
        <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
            <svg class="w-8 h-8 mr-2" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M25.2696 13.126C25.1955 13.6364 24.8589 14.3299 24.4728 14.9328C23.9856 15.6936 23.2125 16.2264 22.3276 16.4114L18.43 17.2265C17.8035 17.3575 17.2899 17.8139 17.0918 18.4189L15.8089 22.2194C15.5528 23.0001 15.0349 23.6769 14.2728 24.0484C13.3171 24.5128 12.146 24.2945 11.4136 23.5765L8.04929 20.2862C7.55599 19.8038 6.88498 19.5421 6.18974 19.5595L4.00072 19.6134C3.01099 19.6387 2.09459 19.1826 1.5765 18.3894C1.0584 17.5963 1.01985 16.5882 1.47397 15.759L3.27751 12.3758C3.60352 11.7643 3.69856 11.052 3.54202 10.3731L2.86186 7.42969C2.64698 6.49972 2.95438 5.52913 3.6737 4.89469C4.39303 4.26024 5.38866 4.07426 6.28836 4.4023L8.87994 5.34554C9.52424 5.58014 10.2328 5.5472 10.8528 5.25401L13.6812 3.91493C14.5246 3.51572 15.5183 3.56003 16.3208 4.03367C17.1234 4.50731 17.6222 5.34498 17.6645 6.26748L17.7634 8.37983C17.7942 9.0337 18.0942 9.64704 18.5922 10.0723L20.1379 11.3909C20.5765 11.7655 21.1241 11.9868 21.6962 12.0218L24.1765 12.1738C24.7289 12.2074 25.1853 12.5915 25.2696 13.126Z" fill="#1C64F2"/>
            </svg>
            {{ $brandName }}
        </a>
        <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
            <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">{{ $title }}</h1>
                <form class="space-y-4 md:space-y-6" action="{{ $action }}" method="POST">
                    {{ $slot }}
                </form>
            </div>
        </div>
    </div>
</section>

@once
@pushOnce('default-slot')
{{-- Default slot content for login-form --}}
<div>
    <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your email</label>
    <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="name@company.com" required="">
</div>
<div>
    <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
    <input type="password" name="password" id="password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required="">
</div>
<div class="flex items-center justify-between">
    <div class="flex items-start">
        <div class="flex items-center h-5">
            <input id="remember" aria-describedby="remember" type="checkbox" class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800">
        </div>
        <div class="ml-3 text-sm">
            <label for="remember" class="text-gray-500 dark:text-gray-300">Remember me</label>
        </div>
    </div>
    <a href="#" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-500">Forgot password?</a>
</div>
<button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Sign in</button>
<p class="text-sm font-light text-gray-500 dark:text-gray-400">
    Don't have an account yet? <a href="#" class="font-medium text-blue-600 hover:underline dark:text-blue-500">Sign up</a>
</p>
@endPushOnce
@endonce