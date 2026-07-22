<?php

use App\Livewire\Forms\PermissionForm;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public PermissionForm $form;

    #[On('panels.administrator.user-management.permission.create.assign-data')]
    public function assignData(): void
    {
        $this->form->reset();
        $this->resetValidation();

        Flux::modal('user-management.permission.create')->show();
    }

    public function save(): void
    {
        $created = $this->form->store();

        $this->form->reset();

        $this->dispatch('panels.administrator.user-management.permission.index.refresh');

        Flux::modals()->close();

        if ($created === 0) {
            Flux::toast(variant: 'warning', text: __('general.permission_already_exists'));

            return;
        }

        Flux::toast(__('general.permission_created', ['count' => $created]));
    }
};
?>

<flux:modal name="user-management.permission.create" flyout position="right" class="space-y-6 md:w-[28rem]">
    <div>
        <flux:heading size="lg">{{ __('actions.create') }} {{ __('general.permission') }}</flux:heading>
        <flux:subheading>{{ __('general.permissions') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:radio.group wire:model="form.mode" variant="segmented">
            <flux:radio value="builder" label="{{ __('general.permission_builder') }}" icon="list-checks" />
            <flux:radio value="manual" label="{{ __('general.permission_manual') }}" icon="pencil" />
        </flux:radio.group>

        {{-- Builder mode: group + actions with a live preview --}}
        <div class="space-y-6" x-show="$wire.form.mode === 'builder'">
            <flux:input
                wire:model="form.group"
                label="{{ __('general.permission_group') }}"
                description="{{ __('general.permission_group_hint') }}"
                placeholder="{{ __('general.permission_group_placeholder') }}"
                icon="key"
                dir="ltr"
                clearable
            />

            <flux:checkbox.group wire:model="form.actions" label="{{ __('general.permission_actions') }}" class="grid grid-cols-2 gap-2">
                @foreach (\App\Livewire\Forms\PermissionForm::ACTIONS as $action)
                    <flux:checkbox value="{{ $action }}" label="{{ __('actions.'.$action) }}" wire:key="permission-action-{{ $action }}" />
                @endforeach
            </flux:checkbox.group>
            <flux:error name="form.actions" />

            <div x-show="$wire.form.group.trim() !== '' && $wire.form.actions.length > 0" x-cloak class="rounded-xl border border-violet-200 bg-violet-50/50 p-4 dark:border-violet-900 dark:bg-violet-950/20">
                <div class="flex items-center gap-2">
                    <flux:icon.badge-check variant="outline" class="size-4 text-violet-500" />
                    <flux:text size="sm">{{ __('general.permission_preview') }}</flux:text>
                </div>
                <div class="mt-3 flex flex-wrap gap-2" dir="ltr">
                    <template x-for="action in $wire.form.actions" :key="action">
                        <flux:badge size="sm" color="violet">
                            <span x-text="$wire.form.group.trim().replace(/^\.+|\.+$/g, '') + '.' + action"></span>
                        </flux:badge>
                    </template>
                </div>
            </div>
        </div>

        {{-- Manual mode: full permission name --}}
        <div class="space-y-6" x-show="$wire.form.mode === 'manual'" x-cloak>
            <flux:input
                wire:model="form.name"
                label="{{ __('general.permission_name') }}"
                placeholder="{{ __('general.permission_name_placeholder') }}"
                icon="key"
                dir="ltr"
                clearable
            />
        </div>

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
