<li>
    <a href="{{ $href }}" class="{{ $active ? 'block py-2 px-3 text-white bg-brand rounded-sm md:bg-transparent md:text-fg-brand-strong md:p-0' : 'block py-2 px-3 text-heading rounded-sm hover:bg-neutral-secondary-medium md:hover:bg-transparent md:border-0 md:hover:text-fg-brand-strong md:p-0' }}" {{ $active ? 'aria-current=page' : '' }} {{ $attributes }}>{{ $slot }}</a>
</li>
