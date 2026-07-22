<?php

namespace App\Livewire\Forms;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;
use Spatie\Permission\Models\Permission;

class PermissionForm extends Form
{
    public const ACTIONS = ['view', 'create', 'edit', 'delete', 'import', 'export'];

    public ?Permission $permission = null;

    public string $mode = 'builder';

    public string $group = '';

    /** @var array<string> */
    public array $actions = [];

    public string $name = '';

    public function setModel(Permission $permission): void
    {
        $this->permission = $permission;

        $this->mode = 'manual';
        $this->name = $permission->name;
    }

    public function rules(): array
    {
        if ($this->permission !== null || $this->mode === 'manual') {
            return [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('permissions', 'name')
                        ->where('guard_name', 'web')
                        ->ignore($this->permission?->id),
                ],
            ];
        }

        return [
            'group' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+([\-\._][a-z0-9]+)*$/'],
            'actions' => ['required', 'array', 'min:1'],
            'actions.*' => ['string', Rule::in(self::ACTIONS)],
        ];
    }

    /**
     * Create the permission(s) and return how many were actually created.
     */
    public function store(): int
    {
        $this->validate();

        return $this->names()
            ->filter(fn (string $name) => Permission::query()
                ->where('name', $name)
                ->where('guard_name', 'web')
                ->doesntExist())
            ->each(fn (string $name) => Permission::create([
                'name' => $name,
                'guard_name' => 'web',
            ]))
            ->count();
    }

    public function update(): void
    {
        $this->validate();

        $this->permission->update([
            'name' => $this->name,
        ]);
    }

    /**
     * @return Collection<int, string>
     */
    public function names(): Collection
    {
        if ($this->mode === 'manual') {
            return collect([trim($this->name)])->filter()->values();
        }

        $group = trim($this->group, ' .');

        if ($group === '') {
            return collect();
        }

        return collect($this->actions)
            ->filter(fn (string $action) => in_array($action, self::ACTIONS, true))
            ->map(fn (string $action) => "{$group}.{$action}")
            ->unique()
            ->values();
    }
}
