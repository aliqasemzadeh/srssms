@props([
    'placeholder' => null,
])

<div
    x-data="{
        search: '',
        query() {
            return this.search.trim().toLowerCase();
        },
        matches(el) {
            const q = this.query();

            return q === '' || el.textContent.toLowerCase().includes(q);
        },
        showItem(el) {
            const q = this.query();

            if (q === '') {
                return true;
            }

            if (el.textContent.toLowerCase().includes(q)) {
                return true;
            }

            const group = el.closest('[data-sidebar-menu-group]');

            return group?.dataset.sidebarMenuHeading?.toLowerCase().includes(q) ?? false;
        },
        expandGroups(root) {
            if (! this.query()) {
                return;
            }

            root.querySelectorAll('ui-disclosure').forEach((el) => el.setAttribute('open', ''));
        },
    }"
    x-effect="expandGroups($el)"
    {{ $attributes->class('flex flex-col gap-4') }}
>
    <flux:input
        type="search"
        icon="magnifying-glass"
        clearable
        placeholder="{{ $placeholder ?? __('general.search').'...' }}"
        x-model="search"
    />

    {{ $slot }}
</div>
