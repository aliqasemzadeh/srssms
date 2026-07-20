<div class="flex items-start gap-2.5 {{ $position === 'right' ? 'justify-end' : '' }}" {{ $attributes }}>
    @if($avatar && $position === 'left')
        <img class="w-8 h-8 rounded-full" src="{{ $avatar }}" alt="{{ $name ?? 'User' }}">
    @endif
    <div class="flex flex-col w-full max-w-[320px] leading-1.5 p-4 border-default bg-neutral-secondary-medium {{ $position === 'right' ? 'rounded-s-xl rounded-ee-xl' : 'rounded-e-xl rounded-es-xl' }}">
        @if($name || $time)
        <div class="flex items-center space-x-2 rtl:space-x-reverse">
            @if($name)<span class="text-sm font-semibold text-heading">{{ $name }}</span>@endif
            @if($time)<span class="text-sm font-normal text-body">{{ $time }}</span>@endif
        </div>
        @endif
        <p class="text-sm font-normal py-2.5 text-heading">{{ $message ?? $slot }}</p>
    </div>
    @if($avatar && $position === 'right')
        <img class="w-8 h-8 rounded-full" src="{{ $avatar }}" alt="{{ $name ?? 'User' }}">
    @endif
</div>
