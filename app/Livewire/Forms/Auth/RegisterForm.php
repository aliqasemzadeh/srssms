<?php

namespace App\Livewire\Forms\Auth;

use App\Models\User;
use App\Settings\SecuritySettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class RegisterForm extends Form
{
    public string $first_name = '';

    public string $last_name = '';

    public string $mobile = '';

    public string $email = '';

    public string $username = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'ir_mobile', Rule::unique(User::class)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class)],
            'username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique(User::class),
                Rule::notIn(app(SecuritySettings::class)->banned_usernames),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'first_name' => __('general.first_name'),
            'last_name' => __('general.last_name'),
            'mobile' => __('general.mobile'),
            'email' => __('general.email'),
            'username' => __('general.username'),
            'password' => __('general.password'),
        ];
    }

    public function register(): User
    {
        if (! app(SecuritySettings::class)->is_registration_enabled) {
            throw ValidationException::withMessages([
                'form.mobile' => __('general.registration_disabled'),
            ]);
        }

        $validated = $this->validate();

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'mobile' => $validated['mobile'],
            'email' => $validated['email'] ?: null,
            'username' => $validated['username'] ?: null,
            'password' => $validated['password'],
        ]);

        Auth::login($user);

        request()->session()->regenerate();

        return $user;
    }
}
