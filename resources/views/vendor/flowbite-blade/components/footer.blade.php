<footer class="bg-neutral-primary {{ $variant === 'sitemap' ? 'bg-dark' : 'rounded-base shadow-sm' }} {{ $positionClasses }} {{ $sticky ? 'border-t border-default' : '' }}" {{ $attributes }}>
    <div class="w-full max-w-screen-xl mx-auto p-4 md:py-8">
        @if($brand ?? false)
            <div class="sm:flex sm:items-center sm:justify-between">
                {{ $brand }}
                @if($links ?? false)
                    <ul class="flex flex-wrap items-center mb-6 text-sm font-medium text-body sm:mb-0">
                        {{ $links }}
                    </ul>
                @endif
            </div>
            <hr class="my-6 border-default sm:mx-auto lg:my-8" />
        @endif
        @if($sections ?? false)
            <div class="grid grid-cols-2 gap-8 px-4 py-6 lg:py-8 md:grid-cols-4">
                {{ $sections }}
            </div>
            <hr class="my-6 border-default sm:mx-auto lg:my-8" />
        @endif
        {{ $slot }}
        @if($copyright ?? false)
            <span class="block text-sm text-body sm:text-center">{{ $copyright }}</span>
        @endif
        @if($social ?? false)
            <div class="flex mt-4 sm:justify-center sm:mt-0 space-x-5">
                {{ $social }}
            </div>
        @endif
    </div>
</footer>
