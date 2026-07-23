<?php

namespace App\Livewire\Forms;

use Illuminate\Support\Facades\Auth;
use Livewire\Form;

class ChangePasswordForm extends Form
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'current_password' => __('general.current_password'),
            'password' => __('general.new_password'),
            'password_confirmation' => __('general.password_confirmation'),
        ];
    }

    public function update(): void
    {
        $validated = $this->validate();

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset();
    }
}
