@props([
    'title' => '',
    'description' => '',
    'count' => null,
])

<section {{ $attributes->merge(['class' => 'bg-white py-8 antialiased dark:bg-gray-900 md:py-16']) }}>
    <div class="mx-auto max-w-screen-lg px-4 2xl:px-0">
        <div class="lg:flex lg:items-center lg:justify-between lg:gap-4">
            @if ($title)
                <h2 class="shrink-0 text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">
                    {{ $title }}@if($count) ({{ $count }})@endif
                </h2>
            @endif

            @if($description)
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 lg:mt-0">{{ $description }}</p>
            @endif

            @if(isset($search))
                {{ $search }}
            @else
                <form class="mt-4 w-full gap-4 sm:flex sm:items-center sm:justify-end lg:mt-0">
                    <label for="simple-search" class="sr-only">Search</label>
                    <div class="relative w-full flex-1 lg:max-w-sm">
                        <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3">
                            <x-fwb-o-search class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                        </div>
                        <input type="text" id="simple-search" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 ps-9 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500" placeholder="Search Questions & Answers" required />
                    </div>

                    <button type="button" class="mt-4 w-full shrink-0 rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 sm:mt-0 sm:w-auto">Ask a question</button>
                </form>
            @endif
        </div>

        <div class="mt-6 flow-root">
            @if($slot->isEmpty())
                <div class="-my-6 divide-y divide-gray-200 dark:divide-gray-800">
                    {{-- Example Q&A item 1 --}}
                    <div class="space-y-4 py-6">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-0.5">
                                @for($i = 0; $i < 5; $i++)
                                    <x-fwb-s-star class="h-4 w-4 text-yellow-300" />
                                @endfor
                            </div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">5.0</p>
                        </div>
                        <p class="text-base font-normal text-gray-500 dark:text-gray-400">
                            It's a great product. I've been using it for a while now and it works perfectly. I would recommend it to anyone.
                        </p>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            <span class="text-gray-900 dark:text-white">Jese Leos</span> - November 28, 2023
                        </p>
                    </div>

                    {{-- Example Q&A item 2 --}}
                    <div class="space-y-4 py-6">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-0.5">
                                @for($i = 0; $i < 4; $i++)
                                    <x-fwb-s-star class="h-4 w-4 text-yellow-300" />
                                @endfor
                                <x-fwb-s-star class="h-4 w-4 text-gray-300 dark:text-gray-600" />
                            </div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">4.0</p>
                        </div>
                        <p class="text-base font-normal text-gray-500 dark:text-gray-400">
                            Decent product overall. Some minor issues with the build quality, but nothing deal-breaking. Customer support was very helpful.
                        </p>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            <span class="text-gray-900 dark:text-white">Bonnie Green</span> - November 18, 2023
                        </p>
                    </div>
                </div>
            @else
                {{ $slot }}
            @endif
        </div>

        @if(isset($actions))
            <div class="mt-6 flex items-center justify-center lg:justify-start">
                {{ $actions }}
            </div>
        @else
            <div class="mt-6 flex items-center justify-center lg:justify-start">
                <button type="button" class="w-full rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700 sm:w-auto">View more questions</button>
            </div>
        @endif
    </div>
</section>
