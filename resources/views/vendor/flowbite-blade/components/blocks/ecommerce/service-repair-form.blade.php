@props([
    'title' => '',
    'action' => '#',
])

<section {{ $attributes->merge(['class' => 'bg-white py-8 antialiased dark:bg-gray-900 md:py-16']) }}>
    <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
        @if(isset($breadcrumb))
            {{ $breadcrumb }}
        @else
            <nav class="mb-4 flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                    <li class="inline-flex items-center">
                        <a href="#" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-700 dark:text-gray-400 dark:hover:text-white">
                            <x-fwb-o-home class="me-2 h-4 w-4" />
                            Home
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <x-fwb-o-chevron-right class="mx-1 h-4 w-4 text-gray-400 rtl:rotate-180" />
                            <a href="#" class="ms-1 text-sm font-medium text-gray-700 hover:text-blue-700 dark:text-gray-400 dark:hover:text-white md:ms-2">My account</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <x-fwb-o-chevron-right class="mx-1 h-4 w-4 text-gray-400 rtl:rotate-180" />
                            <span class="ms-1 text-sm font-medium text-gray-500 dark:text-gray-400 md:ms-2">Service</span>
                        </div>
                    </li>
                </ol>
            </nav>
        @endif

        @if ($title)
            <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl md:mb-6">{{ $title }}</h2>
        @endif

        <form action="{{ $action }}" class="w-full space-y-6 lg:space-y-8">
            @if(isset($steps))
                {{ $steps }}
            @else
                <div class="space-y-6 sm:space-y-8">
                    <ol class="flex flex-col gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800 sm:justify-center md:flex-row md:items-center lg:gap-6">
                        <li class="flex items-center gap-2 md:flex-1 md:flex-col md:gap-1.5 lg:flex-none">
                            <x-fwb-o-check-circle class="h-5 w-5 text-blue-700 dark:text-blue-500" />
                            <p class="text-sm font-medium leading-tight text-blue-700 dark:text-blue-500">My products</p>
                        </li>

                        <div class="hidden h-px w-8 shrink-0 bg-gray-200 dark:bg-gray-700 md:block xl:w-16"></div>

                        <li class="flex items-center gap-2 md:flex-1 md:flex-col md:gap-1.5 lg:flex-none">
                            <x-fwb-o-check-circle class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                            <p class="text-sm font-medium leading-tight text-gray-500 dark:text-gray-400">Defect reason</p>
                        </li>

                        <div class="hidden h-px w-8 shrink-0 bg-gray-200 dark:bg-gray-700 md:block xl:w-16"></div>

                        <li class="flex items-center gap-2 md:flex-1 md:flex-col md:gap-1.5 lg:flex-none">
                            <x-fwb-o-check-circle class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                            <p class="text-sm font-medium leading-tight text-gray-500 dark:text-gray-400">Select date</p>
                        </li>

                        <div class="hidden h-px w-8 shrink-0 bg-gray-200 dark:bg-gray-700 md:block xl:w-16"></div>

                        <li class="flex items-center gap-2 md:flex-1 md:flex-col md:gap-1.5 lg:flex-none">
                            <x-fwb-o-check-circle class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                            <p class="text-sm font-medium leading-tight text-gray-500 dark:text-gray-400">Confirmation</p>
                        </li>
                    </ol>
                </div>
            @endif

            <div class="space-y-6">
                @if(isset($alert))
                    {{ $alert }}
                @else
                    <div class="mb-4 rounded-lg bg-blue-50 p-4 text-sm text-blue-800 dark:bg-gray-800 dark:text-blue-400 sm:text-base" role="alert">
                        <p class="mb-3 font-medium">What you need to know so that the process of sending the product for service goes smoothly:</p>
                        <ol class="mb-3 list-outside list-decimal space-y-2 ps-4">
                            <li>You can send the products sold for service by completing the product repair form.</li>
                            <li>Make sure that the product you want to send for service has an electronic warranty certificate, which you can find in the My Warranties section of your customer account.</li>
                            <li>If the product appears with a printed warranty certificate provided by the manufacturer, contact one of the authorized services specified in it.</li>
                        </ol>
                    </div>
                @endif

                @if($slot->isEmpty())
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">1. Select the product you want to repair:</h3>

                    <div class="divide-y divide-gray-200 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:divide-gray-700 dark:border-gray-700 dark:bg-gray-800">
                        {{-- Product item 1 --}}
                        <div class="flex items-center gap-8 p-6 sm:items-start lg:items-center">
                            <div>
                                <input id="service-product1" type="checkbox" value="" class="h-4 w-4 rounded border-gray-300 bg-gray-100 text-blue-700 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600" />
                                <label for="service-product1" class="sr-only">Product 1</label>
                            </div>
                            <div class="min-w-0 flex-1 gap-14 xl:flex xl:items-center">
                                <div class="min-w-0 max-w-xl flex-1 gap-6 sm:flex sm:items-center">
                                    <a href="#" class="mb-4 flex aspect-square h-14 w-14 shrink-0 items-center sm:mb-0">
                                        <img class="h-auto max-h-full w-full dark:hidden" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front.svg" alt="imac image" />
                                        <img class="hidden h-auto max-h-full w-full dark:block" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front-dark.svg" alt="imac image" />
                                    </a>
                                    <a href="#" class="mt-4 font-medium text-gray-900 hover:underline dark:text-white sm:mt-0">PC system All in One APPLE iMac (2023) mqrq3ro/a, Apple M3, 24" Retina 4.5K, 8GB, SSD 256GB, 10-core GPU</a>
                                </div>
                                <div class="mt-4 flex shrink-0 flex-col gap-2 sm:flex-row sm:justify-between md:items-center xl:mt-0 xl:flex-col xl:items-start">
                                    <dl class="flex items-center gap-2.5">
                                        <dt class="text-base font-normal text-gray-500 dark:text-gray-400 xl:w-36">Order Number:</dt>
                                        <dd class="text-base font-normal text-gray-500 dark:text-gray-400"><a href="#" class="hover:underline">#73742364</a></dd>
                                    </dl>
                                    <dl class="flex items-center gap-2.5">
                                        <dt class="text-base font-normal text-gray-500 dark:text-gray-400 xl:w-36">Return Term:</dt>
                                        <dd class="text-base font-normal text-gray-500 dark:text-gray-400">21.07.2023</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        {{-- Product item 2 --}}
                        <div class="flex items-center gap-8 p-6 sm:items-start lg:items-center">
                            <div>
                                <input checked id="service-product2" type="checkbox" value="" class="h-4 w-4 rounded border-gray-300 bg-gray-100 text-blue-700 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600" />
                                <label for="service-product2" class="sr-only">Product 2</label>
                            </div>
                            <div class="min-w-0 flex-1 gap-14 xl:flex xl:items-center">
                                <div class="min-w-0 max-w-xl flex-1 gap-6 sm:flex sm:items-center">
                                    <a href="#" class="mb-4 flex aspect-square h-14 w-14 shrink-0 items-center sm:mb-0">
                                        <img class="h-auto max-h-full w-full dark:hidden" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/ps5-light.svg" alt="ps5 image" />
                                        <img class="hidden h-auto max-h-full w-full dark:block" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/ps5-dark.svg" alt="ps5 image" />
                                    </a>
                                    <a href="#" class="mt-4 font-medium text-gray-900 hover:underline dark:text-white sm:mt-0">Sony Playstation 5 Digital Edition Console with Extra Blue Controller, and White PULSE 3D Headset</a>
                                </div>
                                <div class="mt-4 flex shrink-0 flex-col gap-2 sm:flex-row sm:justify-between md:items-center xl:mt-0 xl:flex-col xl:items-start">
                                    <dl class="flex items-center gap-2.5">
                                        <dt class="text-gray-500 dark:text-gray-400 xl:w-36">Order Number:</dt>
                                        <dd class="text-base font-normal text-gray-500 dark:text-gray-400"><a href="#" class="hover:underline">#54628495</a></dd>
                                    </dl>
                                    <dl class="flex items-center gap-2.5">
                                        <dt class="text-gray-500 dark:text-gray-400 xl:w-36">Return Term:</dt>
                                        <dd class="text-base font-normal text-gray-500 dark:text-gray-400">24.07.2023</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="gap-4 sm:flex sm:items-center sm:justify-between">
                        <button type="button" class="w-full rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700 sm:w-auto">Cancel</button>
                        <button type="submit" class="mt-4 flex w-full items-center justify-center rounded-lg border border-blue-700 bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:border-blue-800 hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:border-blue-700 dark:bg-blue-600 dark:hover:border-blue-700 dark:hover:bg-blue-700 dark:focus:ring-blue-800 sm:mt-0 sm:w-auto">Next: Return reason</button>
                    </div>
                @else
                    {{ $slot }}
                @endif
            </div>
        </form>
    </div>
</section>
