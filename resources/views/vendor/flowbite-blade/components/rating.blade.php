<div class="flex items-center" {{ $attributes }}>
    @for ($i = 0; $i < $filledStars(); $i++)
        <x-fwb-s-star class="{{ $sizeClasses() }} text-warning" />
    @endfor

    @if ($hasHalfStar())
        <svg class="{{ $sizeClasses() }} text-warning" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 20">
            <defs>
                <linearGradient id="half-star-{{ $value }}">
                    <stop offset="50%" stop-color="currentColor"/>
                    <stop offset="50%" stop-color="#D1D5DB"/>
                </linearGradient>
            </defs>
            <path fill="url(#half-star-{{ $value }})" d="M20.924 7.625a1.523 1.523 0 0 0-1.238-1.044l-5.051-.734-2.259-4.577a1.534 1.534 0 0 0-2.752 0L7.365 5.847l-5.051.734A1.535 1.535 0 0 0 1.463 9.2l3.656 3.563-.863 5.031a1.532 1.532 0 0 0 2.226 1.616L11 17.033l4.518 2.375a1.534 1.534 0 0 0 2.226-1.617l-.863-5.03L20.537 9.2a1.523 1.523 0 0 0 .387-1.575Z"/>
        </svg>
    @endif

    @for ($i = 0; $i < $emptyStars(); $i++)
        <x-fwb-s-star class="{{ $sizeClasses() }} text-neutral-tertiary" />
    @endfor

    @if ($showValue)
        <p class="ms-1 text-sm font-medium text-body">{{ $value }}</p>
        <p class="ms-1 text-sm font-medium text-body">out of</p>
        <p class="ms-1 text-sm font-medium text-body">{{ $max }}</p>
    @endif

    @if ($count !== null)
        <span class="ms-2 text-sm font-medium text-body">({{ $count }})</span>
    @endif
</div>
