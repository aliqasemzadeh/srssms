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
        expandGroups() {
            if (! this.query()) {
                return;
            }

            this.$el.querySelectorAll('ui-disclosure').forEach((el) => {
                el.setAttribute('open', '');

                if ('open' in el) {
                    el.open = true;
                }

                el.querySelectorAll(':scope > div.relative').forEach((panel) => {
                    panel.setAttribute('data-open', '');
                });
            });
        },
    }"
    x-effect="$nextTick(() => expandGroups())"
    {{ $attributes->class('flex flex-col gap-2') }}
>
    <flux:input
        type="search"
        size="sm"
        variant="filled"
        icon="magnifying-glass"
        clearable
        placeholder="{{ $placeholder ?? __('general.search').'...' }}"
        x-model="search"
        x-on:input="expandGroups()"
    />

    {{ $slot }}
</div>
