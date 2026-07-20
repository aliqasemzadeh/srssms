<div role="status" class="animate-pulse" {{ $attributes }}>
    @switch($variant)
        @case('text')
            <div class="h-2.5 bg-neutral-tertiary rounded-full w-48 mb-4"></div>
            <div class="h-2 bg-neutral-tertiary rounded-full max-w-[360px] mb-2.5"></div>
            <div class="h-2 bg-neutral-tertiary rounded-full mb-2.5"></div>
            <div class="h-2 bg-neutral-tertiary rounded-full max-w-[330px] mb-2.5"></div>
            <div class="h-2 bg-neutral-tertiary rounded-full max-w-[300px] mb-2.5"></div>
            <div class="h-2 bg-neutral-tertiary rounded-full max-w-[360px]"></div>
            @break
        @case('card')
            <div class="flex items-center justify-center h-48 mb-4 bg-neutral-tertiary-medium rounded-sm">
                <x-fwb-s-file-image class="w-10 h-10 text-neutral-tertiary" />
            </div>
            <div class="h-2.5 bg-neutral-tertiary rounded-full w-48 mb-4"></div>
            <div class="h-2 bg-neutral-tertiary rounded-full mb-2.5"></div>
            <div class="h-2 bg-neutral-tertiary rounded-full mb-2.5"></div>
            <div class="h-2 bg-neutral-tertiary rounded-full"></div>
            @break
        @case('image')
            <div class="flex items-center justify-center w-full h-48 bg-neutral-tertiary-medium rounded-sm">
                <x-fwb-s-image class="w-10 h-10 text-neutral-tertiary" />
            </div>
            @break
        @case('video')
            <div class="flex items-center justify-center w-full h-48 bg-neutral-tertiary-medium rounded-sm">
                <x-fwb-o-file-video class="w-10 h-10 text-neutral-tertiary" />
            </div>
            @break
        @case('list')
            @for($i = 0; $i < 5; $i++)
            <div class="flex items-center {{ $i < 4 ? 'mb-4' : '' }}">
                <x-fwb-s-user-circle class="w-8 h-8 text-neutral-tertiary me-4" />
                <div>
                    <div class="h-2.5 bg-neutral-tertiary rounded-full w-32 mb-2"></div>
                    <div class="w-48 h-2 bg-neutral-tertiary rounded-full"></div>
                </div>
            </div>
            @endfor
            @break
        @case('widget')
            <div class="h-2.5 bg-neutral-tertiary rounded-full w-32 mb-2.5"></div>
            <div class="w-48 h-2 mb-10 bg-neutral-tertiary rounded-full"></div>
            <div class="flex items-baseline mt-4">
                <div class="w-full bg-neutral-tertiary rounded-t-base h-72"></div>
                <div class="w-full h-56 ms-6 bg-neutral-tertiary rounded-t-base"></div>
                <div class="w-full bg-neutral-tertiary rounded-t-base h-72 ms-6"></div>
                <div class="w-full h-64 ms-6 bg-neutral-tertiary rounded-t-base"></div>
                <div class="w-full bg-neutral-tertiary rounded-t-base h-80 ms-6"></div>
                <div class="w-full bg-neutral-tertiary rounded-t-base h-72 ms-6"></div>
                <div class="w-full bg-neutral-tertiary rounded-t-base h-80 ms-6"></div>
            </div>
            @break
        @case('testimonial')
            <div class="h-2.5 bg-neutral-tertiary rounded-full max-w-[640px] mb-2.5 mx-auto"></div>
            <div class="h-2.5 mx-auto bg-neutral-tertiary-medium rounded-full max-w-[540px]"></div>
            <div class="flex items-center justify-center mt-4">
                <x-fwb-s-user-circle class="w-8 h-8 text-neutral-tertiary me-4" />
                <div class="w-20 h-2.5 bg-neutral-tertiary rounded-full me-3"></div>
                <div class="w-24 h-2 bg-neutral-tertiary rounded-full"></div>
            </div>
            @break
    @endswitch
    <span class="sr-only">Loading...</span>
</div>
