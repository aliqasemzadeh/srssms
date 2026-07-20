@props([
    'title' => '',
    'refundId' => '',
])

<section {{ $attributes->merge(['class' => 'bg-white py-8 antialiased dark:bg-gray-900 md:py-16']) }}>
    <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
        <div class="mx-auto max-w-3xl space-y-6 sm:space-y-8">
            @if ($title || $refundId)
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">
                    {{ $title }}
                    @if($refundId)
                        <span class="text-gray-500 dark:text-gray-400">({{ $refundId }})</span>
                    @endif
                </h2>
            @endif

            @if(isset($product))
                {{ $product }}
            @else
                <div class="items-center gap-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:flex">
                    <a href="#" class="mb-4 flex aspect-square h-14 w-14 shrink-0 items-center sm:mb-0">
                        <img class="h-auto max-h-full w-full dark:hidden" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front.svg" alt="imac image" />
                        <img class="hidden h-auto max-h-full w-full dark:block" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front-dark.svg" alt="imac image" />
                    </a>
                    <a href="#" class="min-w-0 flex-1 font-medium text-gray-900 hover:underline dark:text-white">
                        Sony Playstation 5 Digital Edition Console with Extra Blue Controller, White PULSE 3D Headset and Surge Dual Controller Charge Docker
                    </a>
                </div>
            @endif

            @if($slot->isEmpty())
                <ol class="relative border-s border-gray-200 dark:border-gray-700">
                    {{-- Step 1 - Completed --}}
                    <li class="mb-10 ms-6">
                        <span class="absolute -start-2.5 flex h-5 w-5 items-center justify-center rounded-full bg-blue-100 ring-8 ring-white dark:bg-blue-900 dark:ring-gray-900">
                            <x-fwb-o-check class="h-3 w-3 text-blue-800 dark:text-blue-300" />
                        </span>
                        <span class="inline-flex items-center rounded bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                            <x-fwb-o-clock class="me-1 h-3 w-3" />
                            02 February 2024
                        </span>
                        <h3 class="mb-0.5 mt-2 text-lg font-semibold text-blue-800 dark:text-blue-300">
                            Your request has been registered
                        </h3>
                        <p class="text-base font-normal text-blue-700 dark:text-blue-300">
                            Please pack the product and accessories received in the original packaging. The courier will contact you to pick up the package from the specified address.
                        </p>
                    </li>

                    {{-- Step 2 - Pending --}}
                    <li class="mb-10 ms-6">
                        <span class="absolute -start-2.5 flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 ring-8 ring-white dark:bg-gray-800 dark:ring-gray-900">
                            <x-fwb-o-check class="h-3 w-3 text-gray-500 dark:text-gray-400" />
                        </span>
                        <h3 class="mb-1.5 text-lg font-semibold leading-none text-gray-900 dark:text-white">
                            Pick up product from the address
                        </h3>
                        <p class="text-base font-normal text-gray-500 dark:text-gray-400">
                            Estimated time 2 February 2024 - 5 February 2024.
                        </p>
                    </li>

                    {{-- Step 3 - Pending --}}
                    <li class="mb-10 ms-6">
                        <span class="absolute -start-2.5 flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 ring-8 ring-white dark:bg-gray-800 dark:ring-gray-900">
                            <x-fwb-o-check class="h-3 w-3 text-gray-500 dark:text-gray-400" />
                        </span>
                        <h3 class="mb-1.5 text-lg font-semibold leading-none text-gray-900 dark:text-white">
                            Product check
                        </h3>
                        <p class="text-base font-normal text-gray-500 dark:text-gray-400">
                            We will carefully check the product and inform you as soon as possible if you are eligible for a refund.
                        </p>
                    </li>

                    {{-- Step 4 - Pending --}}
                    <li class="ms-6">
                        <span class="absolute -start-2.5 flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 ring-8 ring-white dark:bg-gray-800 dark:ring-gray-900">
                            <x-fwb-o-check class="h-3 w-3 text-gray-500 dark:text-gray-400" />
                        </span>
                        <h3 class="mb-1.5 text-lg font-semibold leading-none text-gray-900 dark:text-white">
                            Refund the amount
                        </h3>
                        <p class="text-base font-normal text-gray-500 dark:text-gray-400">
                            We will return the amount depending on the option chosen.
                        </p>
                    </li>
                </ol>
            @else
                {{ $slot }}
            @endif

            @if(isset($actions))
                {{ $actions }}
            @else
                <div class="sm:flex items-center sm:space-x-4 space-y-4 sm:space-y-0">
                    <a href="#" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700" role="button">
                        <x-fwb-o-arrow-left class="h-5 w-5" />
                        Back to your account
                    </a>
                    <button type="button" class="w-full sm:w-auto flex justify-center items-center rounded-lg bg-red-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">
                        <x-fwb-o-close class="-ms-2 me-2 h-5 w-5" />
                        Cancel the refund
                    </button>
                </div>
            @endif
        </div>
    </div>
</section>
