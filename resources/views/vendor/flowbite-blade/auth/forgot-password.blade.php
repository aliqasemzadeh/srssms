<x-fwb.layouts.auth :title="__('Forgot password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Forgot password')" :description="__('Enter your mobile number to receive a password reset link')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Mobile Number -->
            <div>
                <x-fwb.input
                    name="mobile"
                    :label="__('Mobile number')"
                    :value="old('mobile')"
                    type="tel"
                    required
                    autofocus
                    autocomplete="tel"
                    placeholder="09123456789"
                />
                @error('mobile')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <x-fwb.button type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('Send password reset link') }}
            </x-fwb.button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-gray-500 dark:text-gray-400">
            <span>{{ __('Or, return to') }}</span>
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline dark:text-blue-500" wire:navigate>{{ __('log in') }}</a>
        </div>
    </div>
</x-fwb.layouts.auth>
