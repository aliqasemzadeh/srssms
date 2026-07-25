<?php

use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Models\Sms\Gateway;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use AuthorizesAdministratorPermissions;

    public ?Gateway $gateway = null;

    public ?int $user_id = null;

    public string $userSearch = '';

    #[On('panels.administrator.sms-management.gateway.user.access.assign-data')]
    public function assignData(int $gateway): void
    {
        $this->authorizePermission('sms-management.gateway.user.create');

        $this->gateway = Gateway::query()->findOrFail($gateway);
        $this->user_id = null;
        $this->userSearch = '';
        $this->resetValidation();
        unset($this->users);

        Flux::modal('sms-management.gateway.user.access')->show();
    }

    public function updatedUserSearch(): void
    {
        unset($this->users);
    }

    #[Computed]
    public function users(): Collection
    {
        if (! $this->gateway) {
            return collect();
        }

        $assignedIds = $this->gateway->users()->pluck('users.id');

        return User::query()
            ->whereNotIn('id', $assignedIds)
            ->when($this->userSearch, function ($query) {
                $query->where(function ($query) {
                    $query->where('first_name', 'like', "%{$this->userSearch}%")
                        ->orWhere('last_name', 'like', "%{$this->userSearch}%")
                        ->orWhere('mobile', 'like', "%{$this->userSearch}%")
                        ->orWhere('email', 'like', "%{$this->userSearch}%")
                        ->orWhere('username', 'like', "%{$this->userSearch}%");
                });
            })
            ->orderBy('first_name')
            ->limit(20)
            ->get()
            ->map(fn (User $user) => (object) [
                'id' => $user->id,
                'label' => trim($user->full_name.' — '.($user->mobile ?: $user->email ?: '#'.$user->id)),
            ]);
    }

    public function save(): void
    {
        $this->authorizePermission('sms-management.gateway.user.create');

        if (! $this->gateway) {
            return;
        }

        $this->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('sms_gateway_user', 'user_id')->where('gateway_id', $this->gateway->id),
            ],
        ]);

        $this->gateway->users()->syncWithoutDetaching([$this->user_id]);

        $this->user_id = null;
        $this->userSearch = '';
        unset($this->users);

        $this->dispatch('panels.administrator.sms-management.gateway.user.index.refresh');

        Flux::modals()->close();
        Flux::toast(__('general.gateway_access_granted'));
    }
};
?>

<flux:modal name="sms-management.gateway.user.access" class="min-w-[28rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.gateway_access') }}</flux:heading>
        <flux:subheading>{{ __('general.gateway_access_hint') }}</flux:subheading>
    </div>

    @if ($gateway)
        <flux:callout icon="radio-tower" variant="secondary" inline>
            <flux:callout.heading>
                {{ $gateway->title }} — <span dir="ltr">{{ $gateway->number }}</span>
            </flux:callout.heading>
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-6">
        <flux:select wire:model.live="user_id" variant="combobox" :filter="false" label="{{ __('general.user') }}">
            <x-slot name="input">
                <flux:select.input wire:model.live.debounce.300ms="userSearch" placeholder="{{ __('general.search') }}..." />
            </x-slot>

            @foreach ($this->users as $userOption)
                <flux:select.option value="{{ $userOption->id }}" wire:key="gateway-access-user-{{ $userOption->id }}">
                    {{ $userOption->label }}
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
