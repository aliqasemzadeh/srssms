@props([
    'title' => '',
])

<section {{ $attributes->merge(['class' => 'bg-white py-8 antialiased dark:bg-gray-900 md:py-16']) }}>
    <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
        <div class="mx-auto max-w-5xl">
            <div class="gap-4 sm:flex sm:items-center sm:justify-between">
                @if ($title)
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">{{ $title }}</h2>
                @endif

                @if(isset($filter))
                    {{ $filter }}
                @else
                    <div class="mt-6 sm:mt-0">
                        <label for="review-type" class="sr-only mb-2 block text-sm font-medium text-gray-900 dark:text-white">Select review type</label>
                        <select id="review-type" class="block w-full min-w-[8rem] rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                            <option selected>All reviews</option>
                            <option value="5">5 stars</option>
                            <option value="4">4 stars</option>
                            <option value="3">3 stars</option>
                            <option value="2">2 stars</option>
                            <option value="1">1 star</option>
                        </select>
                    </div>
                @endif
            </div>

            <div class="mt-6 flow-root sm:mt-8">
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @if($slot->isEmpty())
                        {{-- Default review item 1 --}}
                        <div class="grid gap-4 py-4 md:grid-cols-12 md:gap-6 md:py-6">
                            <dl class="md:col-span-3">
                                <dt class="sr-only">Product</dt>
                                <dd class="text-base font-semibold text-gray-900 dark:text-white">
                                    <a href="#" class="hover:underline">Apple iMac 27"</a>
                                </dd>
                            </dl>
                            <dl class="md:col-span-6">
                                <dt class="sr-only">Review</dt>
                                <dd class="text-base font-normal text-gray-500 dark:text-gray-400">
                                    It's fancy, amazing keyboard, matching accessories. But icons determine which keys to press for specific copied characters or special copied.
                                </dd>
                            </dl>
                            <div class="md:col-span-3 flex items-center justify-between">
                                <dl>
                                    <dt class="sr-only">Rating</dt>
                                    <dd class="flex items-center space-x-1">
                                        @for($i = 0; $i < 5; $i++)
                                            <x-fwb-s-star class="h-4 w-4 text-yellow-400" />
                                        @endfor
                                    </dd>
                                </dl>
                                <button type="button" class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <x-fwb-o-dots-horizontal class="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        {{-- Default review item 2 --}}
                        <div class="grid gap-4 py-4 md:grid-cols-12 md:gap-6 md:py-6">
                            <dl class="md:col-span-3">
                                <dt class="sr-only">Product</dt>
                                <dd class="text-base font-semibold text-gray-900 dark:text-white">
                                    <a href="#" class="hover:underline">Apple iPhone 15</a>
                                </dd>
                            </dl>
                            <dl class="md:col-span-6">
                                <dt class="sr-only">Review</dt>
                                <dd class="text-base font-normal text-gray-500 dark:text-gray-400">
                                    The phone is really fast and the camera quality is amazing. I love the new design and the battery life is great.
                                </dd>
                            </dl>
                            <div class="md:col-span-3 flex items-center justify-between">
                                <dl>
                                    <dt class="sr-only">Rating</dt>
                                    <dd class="flex items-center space-x-1">
                                        @for($i = 0; $i < 4; $i++)
                                            <x-fwb-s-star class="h-4 w-4 text-yellow-400" />
                                        @endfor
                                        <x-fwb-s-star class="h-4 w-4 text-gray-300 dark:text-gray-600" />
                                    </dd>
                                </dl>
                                <button type="button" class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <x-fwb-o-dots-horizontal class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                    @else
                        {{ $slot }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
