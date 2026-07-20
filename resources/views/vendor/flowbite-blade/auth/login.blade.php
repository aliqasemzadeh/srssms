<x-fwb.layouts.auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <div>
                <x-fwb.input
                    name="email"
                    :label="__('Email address')"
                    :value="old('email')"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="email@example.com"
                />
                @error('email')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <div class="relative">
                    <x-fwb.input
                        name="password"
                        :label="__('Password')"
                        type="password"
                        required
                        autocomplete="current-password"
                        :placeholder="__('Password')"
                    />

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="absolute top-0 text-sm end-0 text-blue-600 hover:underline dark:text-blue-500" wire:navigate>
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>
                @error('password')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <x-fwb.checkbox name="remember" :label="__('Remember me')" :checked="(bool) old('remember')" />

            <div class="flex items-center justify-end">
                <x-fwb.button type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in') }}
                </x-fwb.button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-gray-500 dark:text-gray-400">
                <span>{{ __('Don\'t have an account?') }}</span>
                <a href="{{ route('register') }}" class="text-blue-600 hover:underline dark:text-blue-500" wire:navigate>{{ __('Sign up') }}</a>
            </div>
        @endif
    </div>
</x-fwb.layouts.auth>
