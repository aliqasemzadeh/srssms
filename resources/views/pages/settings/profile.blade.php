<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    use ProfileValidationRules;

    public string $first_name = '';

    public string $last_name = '';

    public string $username = '';

    public string $mobile = '';

    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();

        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->username = $user->username;
        $this->mobile = $user->mobile;
        $this->email = $user->email ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        if ($validated['email'] === '') {
            $validated['email'] = null;
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($user->isDirty('mobile')) {
            $user->mobile_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        $user = Auth::user();

        return filled($user->email)
            && $user instanceof MustVerifyEmail
            && ! $user->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        $user = Auth::user();

        return ! $user instanceof MustVerifyEmail
            || ! filled($user->email)
            || ($user instanceof MustVerifyEmail && $user->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <h2 class="sr-only">{{ __('Profile Settings') }}</h2>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your profile information')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <x-fwb.input
                    wire:model="first_name"
                    :label="__('First name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="given-name"
                />

                <x-fwb.input
                    wire:model="last_name"
                    :label="__('Last name')"
                    type="text"
                    required
                    autocomplete="family-name"
                />
            </div>

            <x-fwb.input
                wire:model="username"
                :label="__('Username')"
                type="text"
                required
                autocomplete="username"
            />

            <x-fwb.input
                wire:model="mobile"
                :label="__('Mobile number')"
                type="tel"
                required
                autocomplete="tel"
                placeholder="09123456789"
            />

            <div>
                <x-fwb.input
                    wire:model="email"
                    :label="__('Email address')"
                    type="email"
                    autocomplete="email"
                    placeholder="email@example.com"
                />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Your email address is unverified.') }}

                            <a href="#" class="text-sm text-blue-600 hover:underline dark:text-blue-500 cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </a>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <x-fwb.button type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </x-fwb.button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>
