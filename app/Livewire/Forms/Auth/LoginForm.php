<?php

namespace App\Livewire\Forms\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class LoginForm extends Form
{
    public string $loginType = 'mobile';

    public string $mobile = '';

    public string $email = '';

    public string $username = '';

    public string $password = '';

    public bool $remember = false;

    public function updatedLoginType(): void
    {
        $this->resetValidation();
        $this->mobile = '';
        $this->email = '';
        $this->username = '';
    }

    public function rules(): array
    {
        $rules = [
            'password' => ['required', 'string'],
        ];

        return match ($this->loginType) {
            'email' => [
                ...$rules,
                'email' => ['required', 'email'],
            ],
            'username' => [
                ...$rules,
                'username' => ['required', 'string'],
            ],
            default => [
                ...$rules,
                'mobile' => ['required', 'string', 'ir_mobile'],
            ],
        };
    }

    public function validationAttributes(): array
    {
        return [
            'mobile' => __('general.mobile'),
            'email' => __('general.email'),
            'username' => __('general.username'),
            'password' => __('general.password'),
        ];
    }

    public function authenticate(): void
    {
        $this->validate();

        $credentials = match ($this->loginType) {
            'email' => ['email' => $this->email, 'password' => $this->password],
            'username' => ['username' => $this->username, 'password' => $this->password],
            default => ['mobile' => $this->mobile, 'password' => $this->password],
        };

        if (! Auth::attempt($credentials, $this->remember)) {
            throw ValidationException::withMessages([
                'form.'.$this->loginType => __('general.login_failed'),
            ]);
        }

        request()->session()->regenerate();
    }
}
