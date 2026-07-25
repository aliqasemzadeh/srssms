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

    public function mount(): void
    {
        session()->forget('otp.login.user_id');
        $this->step = 'request';
        $this->code = '';
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

        Flux::toast(__('general.otp_sent'));
    }

    public function resend(OtpService $otp): void
    {
        $user = $this->loginUser();

        if ($user) {
            $otp->send($user, 'sms', OtpService::PURPOSE_LOGIN);
        }

        $this->code = '';

        Flux::toast(__('general.otp_sent'));
    }

    public function verify(): void
    {
        $user = $this->loginUser();

        if (! $user) {
            throw ValidationException::withMessages([
                'code' => __('general.otp_invalid'),
            ]);
        }

        $this->validate([
            'code' => ['required', 'digits:6'],
        ], attributes: [
            'code' => __('general.verify_otp'),
        ]);

        $result = $user->attemptLoginUsingOneTimePassword($this->code, remember: true);

        if (! $result->isOk()) {
            throw ValidationException::withMessages([
                'code' => $result->validationMessage() ?: __('general.otp_invalid'),
            ]);
        }

        session()->forget('otp.login.user_id');
        request()->session()->regenerate();

        Flux::toast(__('general.login_success'));

        $this->redirectIntended('/');
    }

    protected function loginUser(): ?User
    {
        $userId = session('otp.login.user_id');

        return $userId ? User::query()->find($userId) : null;
    }

    public function resetToRequest(): void
    {
        session()->forget('otp.login.user_id');
        $this->step = 'request';
        $this->code = '';
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.login_with_otp') }} - {{ config('app.name') }}</x-slot>

    @if ($step === 'request')
        <form wire:submit="send" class="space-y-4">
            <div class="space-y-2 text-center">
                <flux:heading size="lg">{{ __('general.login_with_otp') }}</flux:heading>
                <flux:text>{{ __('general.login_with_otp_hint') }}</flux:text>
            </div>

            <flux:field>
                <flux:label>{{ __('general.mobile') }}</flux:label>
                <flux:input type="text" wire:model="mobile" icon="phone" placeholder="09123456789" />
                <flux:error name="mobile" />
                <flux:error name="otp" />
            </flux:field>

            <flux:button type="submit" variant="primary" color="teal" class="w-full">
                {{ __('general.send_otp') }}
            </flux:button>
        </form>
    @else
        <form wire:submit="verify" class="space-y-8">
            <div class="max-w-64 mx-auto space-y-2">
                <flux:heading size="lg" class="text-center">{{ __('general.otp_verify_heading') }}</flux:heading>
                <flux:text class="text-center">{{ __('general.otp_verify_hint') }}</flux:text>
            </div>

            <div class="space-y-6">
                <flux:otp wire:model="code" length="6" submit="auto" dir="ltr" class="mx-auto" />
                <flux:error name="code" />
                <flux:error name="otp" />
            </div>

            <flux:button type="submit" variant="primary" color="teal" class="w-full">
                {{ __('general.verify_otp') }}
            </flux:button>

            <div class="space-y-2 text-center">
                <flux:button type="button" variant="ghost" wire:click="resend" class="w-full">
                    {{ __('general.resend_otp') }}
                </flux:button>
                <flux:button type="button" variant="ghost" wire:click="resetToRequest" class="w-full">
                    {{ __('general.change_mobile') }}
                </flux:button>
            </div>
        </form>
    @endif

    <div class="mt-4 space-y-2 text-center text-sm text-zinc-500 dark:text-zinc-400">
        <div>
            <flux:link href="{{ route('login') }}" wire:navigate class="font-medium">
                {{ __('general.back_to_login') }}
            </flux:link>
        </div>
    </div>
</div>
