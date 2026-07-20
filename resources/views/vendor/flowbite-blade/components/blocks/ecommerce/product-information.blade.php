@props([
    'title' => '',
])

<section {{ $attributes->merge(['class' => 'bg-white py-8 antialiased dark:bg-gray-900 md:py-16']) }}>
    <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
        <div class="mx-auto max-w-5xl">
            @if ($title)
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">{{ $title }}</h2>
            @endif

            @if(isset($media))
                {{ $media }}
            @else
                <div class="my-8 xl:mb-16 xl:mt-12">
                    <img class="w-full dark:hidden" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-showcase.svg" alt="" />
                    <img class="hidden w-full dark:block" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-showcase-dark.svg" alt="" />
                </div>
            @endif

            @if($slot->isEmpty())
                <div class="mx-auto max-w-2xl space-y-6">
                    <p class="text-base font-normal text-gray-500 dark:text-gray-400">The iMac "M1" features a 5-nm Apple M1 processor with 8 cores, an 8-core GPU, a 16-core Neural Engine, 8 GB RAM, and a 1 TB onboard SSD.</p>

                    <p class="text-base font-normal text-gray-500 dark:text-gray-400">
                        This all is housed in a wafer thin aluminum case with flat edges that includes a 23.5" 4480x2520, 218 PPI, LED-backlit, "True Tone" widescreen "Retina 4.5K" display mounted on a svelte aluminum stand.
                    </p>

                    <p class="text-base font-semibold text-gray-900 dark:text-white">Key Features and Benefits:</p>
                    <ul class="list-outside list-disc space-y-4 pl-4 text-base font-normal text-gray-500 dark:text-gray-400">
                        <li>
                            <span class="font-semibold text-gray-900 dark:text-white">Brilliant 4.5K Retina display:</span>
                            See it all in sharp, glorious detail on the immersive 24-inch 4.5K Retina display with P3 wide color gamut and 500 nits of brightness.
                        </li>
                        <li>
                            <span class="font-semibold text-gray-900 dark:text-white">1080p FaceTime HD camera:</span>
                            Double the resolution for higher-quality video calls with advanced image signal processor improvements.
                        </li>
                        <li>
                            <span class="font-semibold text-gray-900 dark:text-white">Studio-quality mics for high-quality conversations:</span>
                            The studio-quality three-mic array reduces feedback with beamforming technology to ignore background noise.
                        </li>
                        <li>
                            <span class="font-semibold text-gray-900 dark:text-white">Six-speaker sound system:</span>
                            Two pairs of force-canceling woofers create rich bass balanced with high-performance tweeters.
                        </li>
                        <li>
                            <span class="font-semibold text-gray-900 dark:text-white">M1 chip: with great power comes great capability:</span>
                            M1 delivers up to 85 percent faster CPU performance and up to two times faster graphics performance than previous models.
                        </li>
                    </ul>
                </div>

                @if(isset($video))
                    <div class="my-6 md:my-12">
                        {{ $video }}
                    </div>
                @endif

                <div class="mx-auto mb-6 max-w-3xl space-y-6 md:mb-12">
                    <p class="text-base font-normal text-gray-500 dark:text-gray-400">Connectivity includes two Thunderbolt / USB 4 ports and two USB 3 ports (all with a USB-C connector), a 3.5 mm headphone jack, Wi-Fi 6 (802.11ax), and Bluetooth 5.0.</p>

                    <p class="text-base font-normal text-gray-500 dark:text-gray-400">A-Grade/CR: iMacs are in 9/10 Cosmetic Condition and are 100% Fully Functional. 90 Days Seller Warranty Included.</p>
                </div>

                <div class="text-center">
                    <a href="#" class="mb-2 mr-2 rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700">Show more...</a>
                </div>
            @else
                {{ $slot }}
            @endif
        </div>
    </div>
</section>
