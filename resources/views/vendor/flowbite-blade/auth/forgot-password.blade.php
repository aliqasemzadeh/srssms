<x-fwb.layouts.auth :title="__('Forgot password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <div>
                <x-fwb.input
                    name="email"
                    :label="__('Email Address')"
                    type="email"
                    required
                    autofocus
                    placeholder="email@example.com"
                />
                @error('email')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <x-fwb.button type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('Email password reset link') }}
            </x-fwb.button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-gray-500 dark:text-gray-400">
            <span>{{ __('Or, return to') }}</span>
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline dark:text-blue-500" wire:navigate>{{ __('log in') }}</a>
        </div>
    </div>
</x-fwb.layouts.auth>
