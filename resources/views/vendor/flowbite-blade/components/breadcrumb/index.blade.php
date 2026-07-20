<nav {{ $attributes->merge(['class' => $containerClasses]) }} aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
        {{ $slot }}
    </ol>
</nav>
