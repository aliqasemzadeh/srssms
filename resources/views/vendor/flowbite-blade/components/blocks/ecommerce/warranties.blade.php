@props([
    'title' => '',
    'description' => '',
])

<section {{ $attributes->merge(['class' => 'bg-white py-8 antialiased dark:bg-gray-900 md:py-16']) }}>
    <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
        <div class="mx-auto max-w-5xl">
            <div class="gap-4 sm:flex sm:items-center sm:justify-between">
                @if ($title)
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">{{ $title }}</h2>
                @endif

                @if($description)
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 sm:mt-0">{{ $description }}</p>
                @endif

                @if(isset($filters))
                    {{ $filters }}
                @else
                    <div class="mt-6 flex items-center space-x-4 sm:mt-0">
                        <div>
                            <label for="warranty-status" class="sr-only mb-2 block text-sm font-medium text-gray-900 dark:text-white">Warranty status</label>
                            <select id="warranty-status" class="block w-full min-w-[8rem] rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                                <option selected>All</option>
                                <option value="pending">Pending</option>
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>
                        <span class="inline-block text-gray-500 dark:text-gray-400"> from </span>
                        <div>
                            <label for="warranty-length" class="sr-only mb-2 block text-sm font-medium text-gray-900 dark:text-white">Select warranty length</label>
                            <select id="warranty-length" class="block w-full min-w-[10rem] rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                                <option selected>Warranty length</option>
                                <option value="12">12 months</option>
                                <option value="24">24 months</option>
                                <option value="48">48 months</option>
                                <option value="60">60 months</option>
                            </select>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-6 flow-root sm:mt-8">
                @if($slot->isEmpty())
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        {{-- Example warranty item 1 --}}
                        <div class="flex flex-wrap items-center gap-y-4 py-6">
                            <dl class="w-1/2 sm:w-1/4 lg:w-auto lg:flex-1">
                                <dt class="text-base font-medium text-gray-500 dark:text-gray-400">Order ID:</dt>
                                <dd class="mt-1.5 text-base font-semibold text-gray-900 dark:text-white">
                                    <a href="#" class="hover:underline">#FWB127364372</a>
                                </dd>
                            </dl>

                            <dl class="w-1/2 sm:w-1/4 lg:w-auto lg:flex-1">
                                <dt class="text-base font-medium text-gray-500 dark:text-gray-400">Date:</dt>
                                <dd class="mt-1.5 text-base font-semibold text-gray-900 dark:text-white">11.12.2023</dd>
                            </dl>

                            <dl class="w-1/2 sm:w-1/4 lg:w-auto lg:flex-1">
                                <dt class="text-base font-medium text-gray-500 dark:text-gray-400">Duration:</dt>
                                <dd class="mt-1.5 text-base font-semibold text-gray-900 dark:text-white">24 months</dd>
                            </dl>

                            <dl class="w-1/2 sm:w-1/4 lg:w-auto lg:flex-1">
                                <dt class="text-base font-medium text-gray-500 dark:text-gray-400">Status:</dt>
                                <dd class="me-2 mt-1.5 inline-flex items-center rounded bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300">
                                    <x-fwb-o-check class="me-1 h-3 w-3" />
                                    Active
                                </dd>
                            </dl>

                            <div class="w-full lg:flex lg:w-64 lg:items-center lg:justify-end lg:gap-4">
                                <a href="#" class="flex w-full items-center justify-center rounded-lg bg-blue-700 px-3 py-2 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 lg:w-auto">View details</a>
                            </div>
                        </div>

                        {{-- Example warranty item 2 --}}
                        <div class="flex flex-wrap items-center gap-y-4 py-6">
                            <dl class="w-1/2 sm:w-1/4 lg:w-auto lg:flex-1">
                                <dt class="text-base font-medium text-gray-500 dark:text-gray-400">Order ID:</dt>
                                <dd class="mt-1.5 text-base font-semibold text-gray-900 dark:text-white">
                                    <a href="#" class="hover:underline">#FWB125467980</a>
                                </dd>
                            </dl>

                            <dl class="w-1/2 sm:w-1/4 lg:w-auto lg:flex-1">
                                <dt class="text-base font-medium text-gray-500 dark:text-gray-400">Date:</dt>
                                <dd class="mt-1.5 text-base font-semibold text-gray-900 dark:text-white">05.01.2023</dd>
                            </dl>

                            <dl class="w-1/2 sm:w-1/4 lg:w-auto lg:flex-1">
                                <dt class="text-base font-medium text-gray-500 dark:text-gray-400">Duration:</dt>
                                <dd class="mt-1.5 text-base font-semibold text-gray-900 dark:text-white">12 months</dd>
                            </dl>

                            <dl class="w-1/2 sm:w-1/4 lg:w-auto lg:flex-1">
                                <dt class="text-base font-medium text-gray-500 dark:text-gray-400">Status:</dt>
                                <dd class="me-2 mt-1.5 inline-flex items-center rounded bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-300">
                                    <x-fwb-o-close class="me-1 h-3 w-3" />
                                    Expired
                                </dd>
                            </dl>

                            <div class="w-full lg:flex lg:w-64 lg:items-center lg:justify-end lg:gap-4">
                                <a href="#" class="flex w-full items-center justify-center rounded-lg bg-blue-700 px-3 py-2 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 lg:w-auto">View details</a>
                            </div>
                        </div>
                    </div>
                @else
                    {{ $slot }}
                @endif
            </div>

            @if(isset($pagination))
                <nav class="mt-6 flex items-center justify-center sm:mt-8" aria-label="Page navigation">
                    {{ $pagination }}
                </nav>
            @endif
        </div>
    </div>
</section>
