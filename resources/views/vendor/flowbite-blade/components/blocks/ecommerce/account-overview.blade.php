@props([
    'title' => '',
])

<section {{ $attributes->merge(['class' => 'bg-white py-8 antialiased dark:bg-gray-900 md:py-8']) }}>
    <div class="mx-auto max-w-screen-lg px-4 2xl:px-0">
        @if(isset($breadcrumb))
            {{ $breadcrumb }}
        @else
            <nav class="mb-4 flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                    <li class="inline-flex items-center">
                        <a href="#" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                            <x-fwb-o-home class="me-2 h-4 w-4" />
                            Home
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <x-fwb-o-chevron-right class="mx-1 h-4 w-4 text-gray-400 rtl:rotate-180" />
                            <a href="#" class="ms-1 text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white md:ms-2">My account</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <x-fwb-o-chevron-right class="mx-1 h-4 w-4 text-gray-400 rtl:rotate-180" />
                            <span class="ms-1 text-sm font-medium text-gray-500 dark:text-gray-400 md:ms-2">Account</span>
                        </div>
                    </li>
                </ol>
            </nav>
        @endif

        @if ($title)
            <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">{{ $title }}</h2>
        @endif

        @if($slot->isEmpty())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {{-- General information --}}
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800 md:p-6">
                    <div class="mb-4 flex items-center gap-2">
                        <x-fwb-o-user class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">General information</h3>
                    </div>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm font-normal text-gray-500 dark:text-gray-400">Name</dt>
                            <dd class="text-base font-semibold text-gray-900 dark:text-white">Jese Leos</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-normal text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="text-base font-semibold text-gray-900 dark:text-white">jese@flowbite.com</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-normal text-gray-500 dark:text-gray-400">Phone</dt>
                            <dd class="text-base font-semibold text-gray-900 dark:text-white">+(123) 456 7890</dd>
                        </div>
                    </dl>
                    <a href="#" class="mt-4 inline-flex items-center text-sm font-medium text-blue-600 hover:underline dark:text-blue-500">
                        Edit your data
                        <x-fwb-o-chevron-right class="ms-1 h-4 w-4" />
                    </a>
                </div>

                {{-- Delivery address --}}
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800 md:p-6">
                    <div class="mb-4 flex items-center gap-2">
                        <x-fwb-o-home class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Delivery address</h3>
                    </div>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm font-normal text-gray-500 dark:text-gray-400">Address</dt>
                            <dd class="text-base font-semibold text-gray-900 dark:text-white">34 Scott Street, San Francisco, California, USA</dd>
                        </div>
                    </dl>
                    <a href="#" class="mt-4 inline-flex items-center text-sm font-medium text-blue-600 hover:underline dark:text-blue-500">
                        Edit address
                        <x-fwb-o-chevron-right class="ms-1 h-4 w-4" />
                    </a>
                </div>

                {{-- Payment methods --}}
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800 md:p-6">
                    <div class="mb-4 flex items-center gap-2">
                        <x-fwb-o-wallet class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment methods</h3>
                    </div>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm font-normal text-gray-500 dark:text-gray-400">Visa ending in 7658</dt>
                            <dd class="text-base font-semibold text-gray-900 dark:text-white">Expiry 10/2024</dd>
                        </div>
                    </dl>
                    <a href="#" class="mt-4 inline-flex items-center text-sm font-medium text-blue-600 hover:underline dark:text-blue-500">
                        Edit payment methods
                        <x-fwb-o-chevron-right class="ms-1 h-4 w-4" />
                    </a>
                </div>
            </div>
        @else
            {{ $slot }}
        @endif
    </div>
</section>
