<?php

namespace App\Livewire\Forms\Phonebook;

use App\Models\Phonebook\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

class GroupForm extends Form
{
    public ?Group $group = null;

    public string $name = '';

    public string $description = '';

    public function setModel(Group $group): void
    {
        $this->group = $group;
        $this->name = $group->name;
        $this->description = (string) ($group->description ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = Auth::id();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('phonebook_groups', 'name')
                    ->where(fn ($query) => $query->where('user_id', $userId)->whereNull('deleted_at'))
                    ->ignore($this->group?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function store(): Group
    {
        $this->validate();

        return Group::query()->create([
            'user_id' => Auth::id(),
            'name' => $this->name,
            'description' => $this->description ?: null,
        ]);
    }

    public function update(): void
    {
        $this->validate();

        if (! $this->group || $this->group->user_id !== Auth::id()) {
            return;
        }

        $this->group->update([
            'name' => $this->name,
            'description' => $this->description ?: null,
        ]);
    }
}
