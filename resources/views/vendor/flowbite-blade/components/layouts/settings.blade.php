<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <nav aria-label="{{ __('Settings') }}">
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('profile.edit') ? 'text-blue-700 bg-blue-50 dark:bg-gray-700 dark:text-blue-500' : 'text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700' }}">
                        {{ __('Profile') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('user-password.edit') }}" wire:navigate class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('user-password.edit') ? 'text-blue-700 bg-blue-50 dark:bg-gray-700 dark:text-blue-500' : 'text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700' }}">
                        {{ __('Password') }}
                    </a>
                </li>
                @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                    <li>
                        <a href="{{ route('two-factor.show') }}" wire:navigate class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('two-factor.show') ? 'text-blue-700 bg-blue-50 dark:bg-gray-700 dark:text-blue-500' : 'text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700' }}">
                            {{ __('Two-Factor Auth') }}
                        </a>
                    </li>
                @endif
                <li>
                    <a href="{{ route('appearance.edit') }}" wire:navigate class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('appearance.edit') ? 'text-blue-700 bg-blue-50 dark:bg-gray-700 dark:text-blue-500' : 'text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700' }}">
                        {{ __('Appearance') }}
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <hr class="border-gray-200 dark:border-gray-700 md:hidden w-full" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $heading ?? '' }}</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subheading ?? '' }}</p>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
