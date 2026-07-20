<x-fwb.layouts.auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- First Name -->
                <div>
                    <x-fwb.input
                        name="first_name"
                        :label="__('First name')"
                        :value="old('first_name')"
                        type="text"
                        required
                        autofocus
                        autocomplete="given-name"
                        :placeholder="__('First name')"
                    />
                    @error('first_name')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Last Name -->
                <div>
                    <x-fwb.input
                        name="last_name"
                        :label="__('Last name')"
                        :value="old('last_name')"
                        type="text"
                        required
                        autocomplete="family-name"
                        :placeholder="__('Last name')"
                    />
                    @error('last_name')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Username -->
            <div>
                <x-fwb.input
                    name="username"
                    :label="__('Username')"
                    :value="old('username')"
                    type="text"
                    required
                    autocomplete="username"
                    :placeholder="__('Username')"
                />
                @error('username')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Mobile Number -->
            <div>
                <x-fwb.input
                    name="mobile"
                    :label="__('Mobile number')"
                    :value="old('mobile')"
                    type="tel"
                    required
                    autocomplete="tel"
                    placeholder="09123456789"
                />
                @error('mobile')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Address -->
            <div>
                <x-fwb.input
                    name="email"
                    :label="__('Email address')"
                    :value="old('email')"
                    type="email"
                    autocomplete="email"
                    placeholder="email@example.com"
                />
                @error('email')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <x-fwb.input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Password')"
                />
                @error('password')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <x-fwb.input
                    name="password_confirmation"
                    :label="__('Confirm password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Confirm password')"
                />
            </div>

            <div class="flex items-center justify-end">
                <x-fwb.button type="submit" class="w-full" data-test="register-user-button">
                    {{ __('Create account') }}
                </x-fwb.button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-gray-500 dark:text-gray-400">
            <span>{{ __('Already have an account?') }}</span>
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline dark:text-blue-500" wire:navigate>{{ __('Log in') }}</a>
        </div>
    </div>
</x-fwb.layouts.auth>
