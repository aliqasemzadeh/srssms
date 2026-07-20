<aside id="{{ $id }}" class="w-64" aria-label="Sidebar" {{ $attributes }}>
    <div class="h-full px-3 py-4 overflow-y-auto bg-neutral-secondary-soft">
        {{ $logo ?? '' }}
        <ul class="space-y-2 font-medium">
            {{ $slot }}
        </ul>
        {{ $cta ?? '' }}
    </div>
</aside>
