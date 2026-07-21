# Project Role & Context
You are an expert full-stack developer working on a Laravel project. Your task is to generate code that strictly adheres to the following project guidelines, tech stack, and architectural rules.

## 1. Tech Stack & Environment
*   **Backend:** PHP 8.4, Laravel 13
*   **Frontend:** TailwindCSS, AlpineJS (for UI interactions)
*   **Livewire:** Version 4 (Do NOT use Volt; use standard `Livewire\Component`)
*   **UI Library:** FluxUI (https://fluxui.dev/)
*   **Icons:** Lucide Icons (https://lucide.dev/icons). Add icons using `php artisan flux:icon icon-name`.

## 2. Architecture & File Structure
*   **Single-File Component Architecture:** ALWAYS write Livewire components as Single-File Components. Place all PHP logic inside a `<?php ... ?>` block at the top of the `.blade.php` file.
*   **File Location:** Save these components directly in `resources/views/components/` or `resources/views/pages/`.
*   **Root Node:** Ensure the HTML portion of the component always has a single root wrapping `<div>`.
*   **Separation of Concerns:** Use AlpineJS for UI manipulation and Livewire strictly for Backend logic.
*   **Livewire Inclusions:** When loading a Livewire component inside a view, always pass a key: `<livewire:component-name :key="$componentId" />`.

## 3. Database & Eloquent Models
*   **Attributes:** Use PHP Attributes like `#[Fillable([])]` and `#[Hidden]` from `Illuminate\Database\Eloquent\Attributes` instead of the traditional `$fillable` or `$hidden` arrays.
*   **Relations:** Always explicitly define Eloquent relationships.
*   **Performance:** Heavily optimize queries and strictly avoid N+1 problems.

## 4. Livewire Logic & State Management
*   **Computed Properties:** Use `#[Computed]` attributes to load data (https://livewire.laravel.com/docs/4.x/attribute-computed).
*   **Forms:** For model forms, use Livewire Form Objects (https://livewire.laravel.com/docs/4.x/forms). Extract them using `php artisan livewire:form ModelForm`. Use a `setModel` method to populate data.
*   **Live Binding:** NEVER use `wire:model.live` in forms unless explicitly requested.
*   **Events:** Never use `protected $listeners`. Use `Livewire\Attributes\On;` and `$this->dispatch('event-name');`.
*   **Event Naming:** Use full explicit names (e.g., `panels.administrator.learning-management.school.edit.assign-data`).
*   **Data Refreshing:** After creating/editing, dispatch a `refresh-data` event and use `#[On('refresh-data')]` on the listing page to reload data.
*   **Notifications:** After any Livewire action, trigger a toast notification: `Flux::toast('message');`.

## 5. FluxUI Component Rules
### Layout & Pages
*   **Page Titles:** Use `<x-slot name="title">Page Title - {{ config('app.name') }}</x-slot>`.
*   **Breadcrumbs:** Always include `<flux:breadcrumbs>`.
*   **Cards:** Use `<flux:card>` for search and filter wrappers.

### Tables & Lists
*   **Component:** Use `<flux:table>` for lists. Implement pagination using `->paginate(config('general.per_page'))`.
*   **Searchable Fields:** Add search inputs at the top of `<flux:table.columns>`. Use `<flux:input wire:model.live.debounce.300ms="search" icon="search" ... />`.

### Modals
*   **Wrapper:** For modal components, do NOT add an outer `<div>`. Just use `<flux:modal>`.
*   **Styling:** Always use flyout right positioning: `<flux:modal flyout position="right">`.
*   **Triggers:** Use `<flux:modal.trigger name="modal-name">` to open modals (especially if passing data).
*   **Buttons:** No "Cancel" buttons are needed in modals. Only use full-width submit buttons (`w-full`).
*   **Control via Livewire:** Open/close modals programmatically using `Flux::modal('confirm')->show();` or `Flux::modals()->close();`.

### Forms & Inputs
*   **Selects:** Use `<flux:select searchable>` for standard searchable dropdowns. Use the backend-search component for database options (https://fluxui.dev/components/select#backend-search).
*   **Pillbox:** Use `https://fluxui.dev/components/pillbox#searchable` for multi-select/search.
*   **Numbers:** Use `<flux:input type="number" />`.
*   **Dates/Times:** Use `<flux:date-picker selectable-header />` and `<flux:time-picker selectable-header />`.
*   **Switches:** For boolean states (e.g., `is_active`), use an inline field:
    `<flux:field variant="inline"><flux:label>Label</flux:label><flux:switch wire:model.live="field_name" /><flux:error name="field_name" /></flux:field>`

### Buttons & Actions
*   **Submit Buttons:** Use `<flux:button type="submit" variant="primary" color="teal">Save</flux:button>`.
*   **Generic Buttons:** Only use `color="zinc"` for generic/neutral buttons.
*   **Icons & Tooltips:** Action buttons (edit/delete/import) MUST be wrapped in `<flux:tooltip>` and use small, icon-only variants (e.g., `size="xs" variant="primary" icon="pencil" icon:variant="outline"`).
*   **Colors:** Edit = `color="blue"`, Delete = `color="red"`, Import = `color="teal"`.
*   **Confirmations:** Always use `wire:confirm="{{ __('general.are_you_sure') }}"` for destructive actions (like delete).

### Data Display
*   Use `<flux:callout icon="cube" variant="secondary" inline>` to display specific records (like permissions, roles, users) inside modals.

## 6. Localization & Permissions
*   **Translations:** ALL text must be translated using `/lang/en/general.php`. If adding new text, define it there. Use `{{ __('general.keyword') }}` in views.
*   **Permissions:** Use Spatie Laravel Permission v6. Add all permission translations to `/lang/fa/permissions.php` and `/lang/en/permissions.php`.

## 7. Reference Examples
**Table Example:**
```html
<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('general.users') }}</flux:heading>
            <flux:modal.trigger name="user-create-modal">
                <flux:button variant="primary" color="teal" icon="plus">
                    {{ __('general.create_user') }}
                </flux:button>
            </flux:modal.trigger>
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." />
            </div>

            <flux:table :paginate="$this->users">
                <flux:table.columns>
                    <flux:table.column>{{ __('general.first_name') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->users as$user)
                        <flux:table.row :key="$user->id">
                            <flux:table.cell>{{ $user->first_name }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.user.edit.assign-data', { user: {{$user->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="primary" color="red" icon="trash" icon:variant="outline" wire:click="delete({{ $user->id }})" wire:confirm="{{ __('general.are_you_sure') }}" />
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
    <livewire:user.create />
    <livewire:user.edit />
</div>
```
