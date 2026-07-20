@props([
    'title' => 'You\'ll be in good company',
])

<section {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900']) }}>
    <div class="py-8 lg:py-16 mx-auto max-w-screen-xl px-4">
        <h2 class="mb-8 lg:mb-16 text-3xl font-extrabold tracking-tight leading-tight text-center text-gray-900 dark:text-white md:text-4xl">{{ $title }}</h2>
        <div class="grid grid-cols-2 gap-8 text-gray-500 sm:gap-12 md:grid-cols-3 lg:grid-cols-6 dark:text-gray-400">
            {{ $slot->isNotEmpty() ? $slot : '' }}
        </div>
    </div>
</section>

@once
@push('slot-defaults')
{{-- Default customer logos slot example:
<a href="#" class="flex justify-center items-center">
    <svg class="h-9 hover:text-gray-900 dark:hover:text-white" viewBox="0 0 2500 757" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <title>Airbnb</title>
        <path d="M2352.45 565.453C2352.45 579.551 2340.86 591.139 2326.76 591.139C2312.66 591.139 2301.08 579.551 2301.08 565.453C2301.08 551.354 2312.66 539.766 2326.76 539.766C2340.86 539.766 2352.45 551.354 2352.45 565.453Z" />
    </svg>
</a>
<a href="#" class="flex justify-center items-center">
    <svg class="h-9 hover:text-gray-900 dark:hover:text-white" viewBox="0 0 86 29" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <title>Google</title>
        <path d="M27.514 14.728C27.514 13.864 27.442 13.036 27.31 12.244H14.028V16.924H21.558C21.226 18.624 20.254 20.068 18.802 21.04V24.508H23.318C26.038 22 27.514 18.672 27.514 14.728Z" />
    </svg>
</a>
<a href="#" class="flex justify-center items-center">
    <svg class="h-8 hover:text-gray-900 dark:hover:text-white" viewBox="0 0 2499.6 578.6" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <title>Microsoft</title>
        <path d="M897.9 473.4V118.5H981v280.3l132.5-173.1h82.4L1074 380l128.7 93.4h-88.4l-103.3-80.5-30 38V473.4H897.9z" />
    </svg>
</a>
<a href="#" class="flex justify-center items-center">
    <svg class="h-9 hover:text-gray-900 dark:hover:text-white" viewBox="0 0 2500 748" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <title>Spotify</title>
        <path d="M1264.63 398.474C1200.59 360.594 1063.21 349.819 918.084 394.674C902.377 399.896 885.732 391.165 880.511 375.458C875.29 359.751 884.019 343.107 899.727 337.886C1063.21 287.665 1219.25 300.157 1296.79 348.957C1310.92 357.396 1315.49 375.645 1307.06 389.782C1298.62 403.445 1280.37 408.527 1264.63 398.474Z" />
    </svg>
</a>
<a href="#" class="flex justify-center items-center">
    <svg class="h-8 hover:text-gray-900 dark:hover:text-white" viewBox="0 0 2500 870" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <title>Mailchimp</title>
        <path d="M1682.61 529.86C1674.21 524.62 1666.96 520.28 1660.48 515.03C1645.02 502.88 1637.78 489 1639.7 474.27C1642.66 451.87 1660.91 436.3 1687.72 434.72C1710.81 433.3 1730.18 444.18 1737.26 462.05C1744.33 480.26 1738.55 501.28 1720.23 518.25C1710.08 527.93 1697.24 535.59 1682.61 529.86Z" />
    </svg>
</a>
<a href="#" class="flex justify-center items-center">
    <svg class="h-8 hover:text-gray-900 dark:hover:text-white" viewBox="0 0 2500 492" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <title>Mashable</title>
        <path d="M0 445.38L183.46 46.62H317.84L276.69 138.41L398.85 445.38H265.38L202.92 252.36L130 445.38H0Z" />
    </svg>
</a>
--}}
@endpush
@endonce
