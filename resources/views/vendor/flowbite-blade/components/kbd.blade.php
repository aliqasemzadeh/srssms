@if($arrow)
<kbd class="inline-flex items-center {{ $sizeClasses }} font-semibold text-heading bg-neutral-secondary-medium border border-default rounded-base" {{ $attributes }}>{{ $slot }}</kbd>
@else
<kbd class="{{ $sizeClasses }} font-semibold text-heading bg-neutral-secondary-medium border border-default rounded-base" {{ $attributes }}>{{ $slot }}</kbd>
@endif
