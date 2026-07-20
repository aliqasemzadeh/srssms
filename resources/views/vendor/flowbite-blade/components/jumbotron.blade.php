<section class="{{ $bgClasses }}" @if($bg && str_starts_with($bg, 'http')) style="background-image: url('{{ $bg }}')" @endif {{ $attributes }}>
    <div class="py-8 px-4 mx-auto max-w-screen-xl {{ $variant === 'default' ? 'text-center lg:py-16' : 'lg:py-16' }}">
        @if($title ?? false)
            <h1 class="mb-4 text-4xl font-extrabold tracking-tight leading-none {{ $bg ? 'text-white' : 'text-heading' }} md:text-5xl lg:text-6xl">{{ $title }}</h1>
        @endif
        @if($subtitle ?? false)
            <p class="mb-8 text-lg font-normal {{ $bg ? 'text-gray-300' : 'text-body' }} lg:text-xl sm:px-16 lg:px-48">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
</section>
