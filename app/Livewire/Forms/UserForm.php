<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Form;

class UserForm extends Form
{
    public ?User $user = null;

    public string $first_name = '';
    public string $last_name = '';
    public string $mobile = '';
    public string $email = '';
    public string $username = '';
    public string $password = '';

    public function setModel(User $user): void
    {
        $this->user = $user;

        $this->first_name = $user->first_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->mobile = $user->mobile ?? '';
        $this->email = $user->email ?? '';
        $this->username = $user->username ?? '';
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', Rule::unique('users', 'mobile')->ignore($this->user?->id)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user?->id)],
            'username' => ['required', 'string', Rule::unique('users', 'username')->ignore($this->user?->id)],
            'password' => [$this->user ? 'nullable' : 'required', 'string', 'min:8'],
        ];
    }

    public function store(): User
    {
        $this->validate();

        return User::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'username' => $this->username,
            'password' => $this->password,
        ]);
    }

    public function update(): void
    {
        $this->validate();

        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'username' => $this->username,
        ];

        if ($this->password) {
            $data['password'] = $this->password;
        }

        $this->user->update($data);
    }
}
