<span class="inline-flex items-center justify-center {{ $sizeClasses() }} {{ $colorClasses() }} {{ $roundedClasses() }} {{ $placementClasses() }} {{ $count !== null ? 'font-bold text-white' : '' }}" {{ $attributes }}>
    @if ($count !== null)
        {{ $count }}
    @endif
</span>
