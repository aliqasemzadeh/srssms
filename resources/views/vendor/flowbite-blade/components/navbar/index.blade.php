<nav {{ $attributes->merge(['class' => $navClasses]) }}>
    <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4 {{ $fluid ? '' : 'container' }}">
        {{ $brand ?? '' }}
        <div class="flex md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
            {{ $actions ?? '' }}
            <button data-collapse-toggle="navbar-{{ $id }}" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-body rounded-base md:hidden hover:bg-neutral-secondary-medium focus:outline-none focus:ring-2 focus:ring-neutral-tertiary" aria-controls="navbar-{{ $id }}" aria-expanded="false">
                <span class="sr-only">Open main menu</span>
                <x-fwb-o-bars class="w-5 h-5" />
            </button>
        </div>
        <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-{{ $id }}">
            <ul class="flex flex-col p-4 md:p-0 mt-4 font-medium border border-default rounded-base bg-neutral-secondary-soft md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0 md:bg-neutral-primary">
                {{ $slot }}
            </ul>
        </div>
    </div>
</nav>
