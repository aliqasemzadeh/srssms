<?php

namespace App\Livewire\Forms\Settings;

use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class GeneralSettingsForm extends Form
{
    public string $site_name = '';

    public string $site_short_name = '';

    public string $site_description = '';

    public ?TemporaryUploadedFile $site_logo = null;

    public ?TemporaryUploadedFile $site_favicon = null;

    public ?string $current_logo = null;

    public ?string $current_favicon = null;

    public function setSettings(GeneralSettings $settings): void
    {
        $this->site_name = $settings->site_name;
        $this->site_short_name = $settings->site_short_name;
        $this->site_description = $settings->site_description;
        $this->current_logo = $settings->site_logo;
        $this->current_favicon = $settings->site_favicon;
        $this->site_logo = null;
        $this->site_favicon = null;
    }

    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:255'],
            'site_short_name' => ['required', 'string', 'max:10'],
            'site_description' => ['nullable', 'string', 'max:500'],
            'site_logo' => ['nullable', 'image', 'max:2048'],
            'site_favicon' => ['nullable', 'file', 'mimes:png,ico,svg,jpg,jpeg', 'max:1024'],
        ];
    }

    public function store(): void
    {
        $this->validate();

        $settings = app(GeneralSettings::class);

        $settings->site_name = $this->site_name;
        $settings->site_short_name = $this->site_short_name;
        $settings->site_description = $this->site_description;

        if ($this->site_logo instanceof TemporaryUploadedFile) {
            if ($settings->site_logo) {
                Storage::disk('public')->delete($settings->site_logo);
            }

            $settings->site_logo = $this->site_logo->store('settings', 'public');
        }

        if ($this->site_favicon instanceof TemporaryUploadedFile) {
            if ($settings->site_favicon) {
                Storage::disk('public')->delete($settings->site_favicon);
            }

            $settings->site_favicon = $this->site_favicon->store('settings', 'public');
        }

        $settings->save();

        $this->setSettings($settings);
    }

    public function removeLogo(): void
    {
        $settings = app(GeneralSettings::class);

        if ($settings->site_logo) {
            Storage::disk('public')->delete($settings->site_logo);
        }

        $settings->site_logo = null;
        $settings->save();

        $this->current_logo = null;
        $this->site_logo = null;
    }

    public function removeFavicon(): void
    {
        $settings = app(GeneralSettings::class);

        if ($settings->site_favicon) {
            Storage::disk('public')->delete($settings->site_favicon);
        }

        $settings->site_favicon = null;
        $settings->save();

        $this->current_favicon = null;
        $this->site_favicon = null;
    }
}
