<?php

use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Livewire\Forms\Settings\WelcomePageSettingsForm;
use App\Settings\WelcomePageSettings;
use Flux\Flux;
use Livewire\Component;

new class extends Component
{
    use AuthorizesAdministratorPermissions;

    public WelcomePageSettingsForm $form;

    public string $newPhrase = '';

    public function mount(WelcomePageSettings $settings): void
    {
        $this->form->setSettings($settings);
    }

    public function addPhrase(): void
    {
        $this->resetErrorBag('newPhrase');

        $value = trim($this->newPhrase);

        if ($value === '') {
            return;
        }

        if (in_array($value, $this->form->typewriter_phrases, true)) {
            $this->addError('newPhrase', __('general.duplicate_item'));

            return;
        }

        $this->form->typewriter_phrases[] = $value;
        $this->newPhrase = '';
    }

    public function removePhrase(int $index): void
    {
        unset($this->form->typewriter_phrases[$index]);

        $this->form->typewriter_phrases = array_values($this->form->typewriter_phrases);
    }

    public function addFeature(): void
    {
        $this->form->features[] = [
            'title' => '',
            'description' => '',
            'icon' => 'message-square',
        ];
    }

    public function removeFeature(int $index): void
    {
        unset($this->form->features[$index]);

        $this->form->features = array_values($this->form->features);
    }

    public function save(): void
    {
        $this->authorizePermission('system-management.setting.edit');

        $this->form->store();

        Flux::toast(__('general.settings_saved'));
    }
};
?>

<div>
    <flux:card class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-teal-100 dark:bg-teal-500/20">
                <flux:icon.home class="size-5 text-teal-600 dark:text-teal-400" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('general.welcome_page_settings') }}</flux:heading>
                <flux:subheading>{{ __('general.welcome_page_settings_hint') }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <flux:textarea
                wire:model="form.hero_subtitle"
                label="{{ __('general.hero_subtitle') }}"
                description="{{ __('general.hero_subtitle_hint') }}"
                rows="2"
            />

            <div class="grid gap-6 md:grid-cols-3">
                <flux:input
                    wire:model="form.typewriter_type_delay"
                    type="number"
                    min="10"
                    max="1000"
                    label="{{ __('general.typewriter_type_delay') }}"
                    description="{{ __('general.typewriter_type_delay_hint') }}"
                />
                <flux:input
                    wire:model="form.typewriter_delete_delay"
                    type="number"
                    min="10"
                    max="1000"
                    label="{{ __('general.typewriter_delete_delay') }}"
                    description="{{ __('general.typewriter_delete_delay_hint') }}"
                />
                <flux:input
                    wire:model="form.typewriter_pause_delay"
                    type="number"
                    min="100"
                    max="10000"
                    label="{{ __('general.typewriter_pause_delay') }}"
                    description="{{ __('general.typewriter_pause_delay_hint') }}"
                />
            </div>

            <flux:field>
                <flux:label>{{ __('general.typewriter_phrases') }}</flux:label>
                <flux:description>{{ __('general.typewriter_phrases_hint') }}</flux:description>
                <flux:input.group>
                    <flux:input
                        wire:model="newPhrase"
                        icon="list"
                        placeholder="{{ __('general.type_and_press_enter') }}"
                        wire:keydown.enter.prevent="addPhrase"
                    />
                    <flux:button type="button" icon="plus" wire:click="addPhrase">{{ __('general.add_phrase') }}</flux:button>
                </flux:input.group>
                <flux:error name="newPhrase" />
                <flux:error name="form.typewriter_phrases" />

                <div class="mt-3 space-y-2">
                    @forelse ($form->typewriter_phrases as $index => $phrase)
                        <div class="flex items-center gap-2" wire:key="phrase-{{ $index }}-{{ md5($phrase) }}">
                            <flux:input wire:model="form.typewriter_phrases.{{ $index }}" class="flex-1" />
                            <flux:tooltip content="{{ __('general.remove') }}">
                                <flux:button
                                    type="button"
                                    size="sm"
                                    variant="primary"
                                    color="red"
                                    icon="trash"
                                    icon:variant="outline"
                                    wire:click="removePhrase({{ $index }})"
                                />
                            </flux:tooltip>
                        </div>
                    @empty
                        <flux:text size="sm">{{ __('general.no_items_added') }}</flux:text>
                    @endforelse
                </div>
            </flux:field>

            <div class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <flux:heading size="sm">{{ __('general.welcome_features') }}</flux:heading>
                        <flux:subheading>{{ __('general.welcome_features_hint') }}</flux:subheading>
                    </div>
                    <flux:button type="button" size="sm" variant="primary" color="teal" icon="plus" wire:click="addFeature">
                        {{ __('general.add_feature') }}
                    </flux:button>
                </div>
                <flux:error name="form.features" />

                <div class="space-y-4">
                    @foreach ($form->features as $index => $feature)
                        <div class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700" wire:key="feature-{{ $index }}">
                            <div class="flex items-start justify-between gap-3">
                                <flux:heading size="sm">{{ __('general.feature') }} #{{ $index + 1 }}</flux:heading>
                                <flux:tooltip content="{{ __('general.remove') }}">
                                    <flux:button
                                        type="button"
                                        size="xs"
                                        variant="primary"
                                        color="red"
                                        icon="trash"
                                        icon:variant="outline"
                                        wire:click="removeFeature({{ $index }})"
                                    />
                                </flux:tooltip>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <flux:input
                                    wire:model="form.features.{{ $index }}.title"
                                    label="{{ __('general.feature_title') }}"
                                />
                                <flux:input
                                    wire:model="form.features.{{ $index }}.icon"
                                    label="{{ __('general.feature_icon') }}"
                                    description="{{ __('general.feature_icon_hint') }}"
                                    class="font-mono"
                                    dir="ltr"
                                />
                            </div>

                            <flux:textarea
                                wire:model="form.features.{{ $index }}.description"
                                label="{{ __('general.feature_description') }}"
                                rows="2"
                            />
                        </div>
                    @endforeach
                </div>
            </div>

            <flux:button type="submit" variant="primary" color="teal" icon="save" class="w-full">
                {{ __('general.save') }}
            </flux:button>
        </form>
    </flux:card>
</div>
