<?php

namespace App\Livewire\Forms\Settings;

use App\Settings\ContactSettings;
use Livewire\Form;

class ContactSettingsForm extends Form
{
    public ?string $address = null;

    /** @var array<int, string> */
    public array $phone_numbers = [];

    public ?string $support_email = null;

    public function setSettings(ContactSettings $settings): void
    {
        $this->address = $settings->address;
        $this->phone_numbers = $settings->phone_numbers;
        $this->support_email = $settings->support_email;
    }

    public function rules(): array
    {
        return [
            'address' => ['nullable', 'string', 'max:500'],
            'phone_numbers' => ['array'],
            'phone_numbers.*' => ['string', 'max:20'],
            'support_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function store(): void
    {
        $this->validate();

        $settings = app(ContactSettings::class);

        $settings->address = $this->address ?: null;
        $settings->phone_numbers = array_values($this->phone_numbers);
        $settings->support_email = $this->support_email ?: null;

        $settings->save();
    }
}
