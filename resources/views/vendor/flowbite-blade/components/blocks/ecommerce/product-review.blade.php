@props([
    'title' => '',
    'averageRating' => '',
])

<section {{ $attributes->merge(['class' => 'bg-white py-8 antialiased dark:bg-gray-900 md:py-16']) }}>
    <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
        @if ($title || $averageRating)
            <div class="flex items-center gap-2">
                @if ($title)
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
                @endif
                @if ($averageRating)
                    <div class="mt-2 flex items-center gap-2 sm:mt-0">
                        <div class="flex items-center gap-0.5">
                            @php
                                $fullStars = floor((float)$averageRating);
                                $hasHalfStar = ((float)$averageRating - $fullStars) >= 0.5;
                            @endphp
                            @for($i = 0; $i < $fullStars; $i++)
                                <x-fwb-s-star class="h-4 w-4 text-yellow-300" />
                            @endfor
                            @for($i = $fullStars + ($hasHalfStar ? 1 : 0); $i < 5; $i++)
                                <x-fwb-s-star class="h-4 w-4 text-gray-300 dark:text-gray-600" />
                            @endfor
                        </div>
                        <p class="text-sm font-medium leading-none text-gray-500 dark:text-gray-400">({{ $averageRating }})</p>
                        @if(isset($reviewCount))
                            {{ $reviewCount }}
                        @else
                            <a href="#" class="text-sm font-medium leading-none text-gray-900 underline hover:no-underline dark:text-white">645 Reviews</a>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <div class="my-6 gap-8 sm:flex sm:items-start md:my-8">
            @if(isset($ratingSummary))
                {{ $ratingSummary }}
            @else
                <div class="shrink-0 space-y-4">
                    @if ($averageRating)
                        <p class="text-2xl font-semibold leading-none text-gray-900 dark:text-white">{{ $averageRating }} out of 5</p>
                    @endif
                    <button type="button" data-modal-target="review-modal" data-modal-toggle="review-modal" class="mb-2 me-2 rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Write a review</button>
                </div>
            @endif

            @if(isset($ratingBars))
                {{ $ratingBars }}
            @else
                <div class="mt-6 min-w-0 flex-1 space-y-3 sm:mt-0">
                    @php
                        $ratings = [
                            ['stars' => 5, 'width' => '20%', 'count' => 239],
                            ['stars' => 4, 'width' => '60%', 'count' => 432],
                            ['stars' => 3, 'width' => '15%', 'count' => 53],
                            ['stars' => 2, 'width' => '5%', 'count' => 32],
                            ['stars' => 1, 'width' => '0%', 'count' => 13],
                        ];
                    @endphp
                    @foreach($ratings as $rating)
                        <div class="flex items-center gap-2">
                            <p class="w-2 shrink-0 text-start text-sm font-medium leading-none text-gray-900 dark:text-white">{{ $rating['stars'] }}</p>
                            <x-fwb-s-star class="h-4 w-4 shrink-0 text-yellow-300" />
                            <div class="h-1.5 w-80 rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-1.5 rounded-full bg-yellow-300" style="width: {{ $rating['width'] }}"></div>
                            </div>
                            <a href="#" class="w-8 shrink-0 text-right text-sm font-medium leading-none text-blue-700 hover:underline dark:text-blue-500 sm:w-auto sm:text-left">{{ $rating['count'] }} <span class="hidden sm:inline">reviews</span></a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if($slot->isEmpty())
            <div class="mt-6 divide-y divide-gray-200 dark:divide-gray-700">
                {{-- Default review 1 --}}
                <div class="gap-3 py-6 sm:flex sm:items-start">
                    <div class="shrink-0 space-y-2 sm:w-48 md:w-72">
                        <div class="flex items-center gap-0.5">
                            @for($i = 0; $i < 5; $i++)
                                <x-fwb-s-star class="h-4 w-4 text-yellow-300" />
                            @endfor
                        </div>
                        <div class="space-y-0.5">
                            <p class="text-base font-semibold text-gray-900 dark:text-white">Jese Leos</p>
                            <p class="text-sm font-normal text-gray-500 dark:text-gray-400">November 18 2023</p>
                        </div>
                    </div>
                    <div class="mt-4 min-w-0 flex-1 space-y-4 sm:mt-0">
                        <p class="text-base font-normal text-gray-500 dark:text-gray-400">
                            It's fancy, amazing keyboard, matching accessories. But icons determine which keys to press for specific copied characters or special characters. Apple's Pro Display XDR delivers stunning HDR content with reference modes.
                        </p>
                    </div>
                </div>

                {{-- Default review 2 --}}
                <div class="gap-3 py-6 sm:flex sm:items-start">
                    <div class="shrink-0 space-y-2 sm:w-48 md:w-72">
                        <div class="flex items-center gap-0.5">
                            @for($i = 0; $i < 4; $i++)
                                <x-fwb-s-star class="h-4 w-4 text-yellow-300" />
                            @endfor
                            <x-fwb-s-star class="h-4 w-4 text-gray-300 dark:text-gray-600" />
                        </div>
                        <div class="space-y-0.5">
                            <p class="text-base font-semibold text-gray-900 dark:text-white">Bonnie Green</p>
                            <p class="text-sm font-normal text-gray-500 dark:text-gray-400">November 10 2023</p>
                        </div>
                    </div>
                    <div class="mt-4 min-w-0 flex-1 space-y-4 sm:mt-0">
                        <p class="text-base font-normal text-gray-500 dark:text-gray-400">
                            Really good product overall. The build quality is exceptional and the performance exceeds expectations. The display is gorgeous with accurate colors and deep blacks. Only minor complaint is the price.
                        </p>
                    </div>
                </div>
            </div>
        @else
            {{ $slot }}
        @endif
    </div>
</section>
