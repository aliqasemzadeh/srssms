<video class="{{ $sizeClasses }} rounded-base" {{ $autoplay ? 'autoplay' : '' }} {{ $muted ? 'muted' : '' }} {{ $controls ? 'controls' : '' }} {{ $attributes }}>
    <source src="{{ $src }}" type="video/mp4">
    Your browser does not support the video tag.
</video>
