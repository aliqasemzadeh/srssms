<?php

use App\Models\User;
use App\Services\Auth\OtpService;
use Flux\Flux;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new #[\Livewire\Attributes\Layout('layouts.auth')] class extends Component
{
    public string $step = 'request';

    public string $mobile = '';

    public string $code = '';

    public bool $remember = false;

    public function mount(): void
    {
        if (session()->has('otp.login.user_id')) {
            $this->step = 'verify';
        }
    }

    public function send(OtpService $otp): void
    {
        $this->validate([
            'mobile' => ['required', 'string', 'ir_mobile'],
        ], attributes: [
            'mobile' => __('general.mobile'),
        ]);

        $user = User::query()->where('mobile', $this->mobile)->first();

        if ($user) {
            $otp->send($user, 'sms', OtpService::PURPOSE_LOGIN);

            session([
                'otp.login.user_id' => $user->id,
            ]);
        } else {
            session()->forget('otp.login.user_id');
        }

        $this->step = 'verify';
        $this->code = '';

        Flux::toast(__('app.otp_sent'));
    }

    public function resend(OtpService $otp): void
    {
        $user = $this->loginUser();

        if ($user) {
            $otp->send($user, 'sms', OtpService::PURPOSE_LOGIN);
        }

        $this->code = '';

        Flux::toast(__('app.otp_sent'));
    }

    public function verify(): void
    {
        $user = $this->loginUser();

        if (! $user) {
            throw ValidationException::withMessages([
                'code' => __('app.otp_invalid'),
            ]);
        }

        $this->validate([
            'code' => ['required', 'digits:6'],
        ], attributes: [
            'code' => __('app.verify_otp'),
        ]);

        $result = $user->attemptLoginUsingOneTimePassword($this->code, $this->remember);

        if (! $result->isOk()) {
            throw ValidationException::withMessages([
                'code' => $result->validationMessage() ?: __('app.otp_invalid'),
            ]);
        }

        session()->forget('otp.login.user_id');
        request()->session()->regenerate();

        Flux::toast(__('general.login_success'));

        return redirect()->intended('/');
    }

    protected function loginUser(): ?User
    {
        $userId = session('otp.login.user_id');

        return $userId ? User::query()->find($userId) : null;
    }

    protected function resetToRequest(): void
    {
        session()->forget('otp.login.user_id');
        $this->step = 'request';
        $this->code = '';
    }
};
?>

<div>
    <x-slot name="title">{{ __('app.login_with_otp') }} - {{ config('app.name') }}</x-slot>

    @if ($step === 'request')
        <form wire:submit="send" class="space-y-4">
            <div class="space-y-2 text-center">
                <flux:heading size="lg">{{ __('app.login_with_otp') }}</flux:heading>
                <flux:text>{{ __('app.login_with_otp_hint') }}</flux:text>
            </div>

            <flux:field>
                <flux:label>{{ __('general.mobile') }}</flux:label>
                <flux:input type="text" wire:model="mobile" icon="phone" placeholder="09123456789" />
                <flux:error name="mobile" />
                <flux:error name="otp" />
            </flux:field>

            <flux:button type="submit" variant="primary" color="teal" class="w-full">
                {{ __('app.send_otp') }}
            </flux:button>
        </form>
    @else
        <form wire:submit="verify" class="space-y-8">
            <div class="max-w-64 mx-auto space-y-2">
                <flux:heading size="lg" class="text-center">{{ __('app.otp_verify_heading') }}</flux:heading>
                <flux:text class="text-center">{{ __('app.otp_verify_hint') }}</flux:text>
            </div>

            <div class="space-y-6">
                <flux:otp wire:model="code" length="6" submit="auto" class="mx-auto" />
                <flux:error name="code" />
                <flux:error name="otp" />
            </div>

            <flux:checkbox wire:model="remember" label="{{ __('general.remember_me') }}" />

            <flux:button type="submit" variant="primary" color="teal" class="w-full">
                {{ __('app.verify_otp') }}
            </flux:button>

            <div class="text-center">
                <flux:button type="button" variant="ghost" wire:click="resend" class="w-full">
                    {{ __('app.resend_otp') }}
                </flux:button>
            </div>
        </form>
    @endif

    <div class="mt-4 space-y-2 text-center text-sm text-zinc-500 dark:text-zinc-400">
        <div>
            <flux:link href="{{ route('login') }}" wire:navigate class="font-medium">
                {{ __('app.back_to_login') }}
            </flux:link>
        </div>
    </div>
</div>
