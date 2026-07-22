<?php

namespace App\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;
use Spatie\Permission\Models\Role;

class RoleForm extends Form
{
    public ?Role $role = null;

    public string $name = '';

    public function setModel(Role $role): void
    {
        $this->role = $role;

        $this->name = $role->name;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->ignore($this->role?->id),
            ],
        ];
    }

    public function store(): Role
    {
        $this->validate();

        return Role::create([
            'name' => $this->name,
            'guard_name' => 'web',
        ]);
    }

    public function update(): void
    {
        $this->validate();

        $this->role->update([
            'name' => $this->name,
        ]);
    }
}
