<?php

use App\Models\User;
use App\Services\Auth\OtpService;
use Flux\Flux;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new #[\Livewire\Attributes\Layout('layouts.auth')] class extends Component
{
    public string $step = 'request';

    public string $identifierType = 'mobile';

    public string $mobile = '';

    public string $email = '';

    public string $code = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        if (session('otp.password_reset.verified_at') && session('otp.password_reset.user_id')) {
            $this->step = 'reset';

            return;
        }

        if (session('otp.password_reset.user_id')) {
            $this->step = 'verify';
            $this->identifierType = session('otp.password_reset.channel') === 'mail' ? 'email' : 'mobile';
        }
    }

    public function updatedIdentifierType(): void
    {
        $this->resetValidation();
        $this->mobile = '';
        $this->email = '';
    }

    public function send(OtpService $otp): void
    {
        $rules = match ($this->identifierType) {
            'email' => ['email' => ['required', 'email']],
            default => ['mobile' => ['required', 'string', 'ir_mobile']],
        };

        $this->validate($rules, attributes: [
            'mobile' => __('general.mobile'),
            'email' => __('general.email'),
        ]);

        $user = match ($this->identifierType) {
            'email' => User::query()->where('email', $this->email)->first(),
            default => User::query()->where('mobile', $this->mobile)->first(),
        };

        if ($user) {
            $channel = $this->identifierType === 'email' ? 'mail' : 'sms';

            $otp->send($user, $channel, OtpService::PURPOSE_PASSWORD_RESET);

            session([
                'otp.password_reset.user_id' => $user->id,
                'otp.password_reset.channel' => $channel,
            ]);
            session()->forget('otp.password_reset.verified_at');
        } else {
            session()->forget([
                'otp.password_reset.user_id',
                'otp.password_reset.channel',
                'otp.password_reset.verified_at',
            ]);
        }

        $this->step = 'verify';
        $this->code = '';

        Flux::toast(__('general.otp_sent'));
    }

    public function resend(OtpService $otp): void
    {
        $user = $this->resetUser();

        if ($user) {
            $channel = session('otp.password_reset.channel', 'sms');
            $otp->send($user, $channel, OtpService::PURPOSE_PASSWORD_RESET);
        }

        $this->code = '';

        Flux::toast(__('general.otp_sent'));
    }

    public function verify(): void
    {
        $user = $this->resetUser();

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

        $result = $user->consumeOneTimePassword($this->code);

        if (! $result->isOk()) {
            throw ValidationException::withMessages([
                'code' => $result->validationMessage() ?: __('general.otp_invalid'),
            ]);
        }

        session([
            'otp.password_reset.verified_at' => now()->timestamp,
        ]);

        $this->step = 'reset';
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function resetPassword(): void
    {
        $user = $this->resetUser();
        $verifiedAt = session('otp.password_reset.verified_at');

        if (! $user || ! $verifiedAt || $verifiedAt < now()->subMinutes(10)->timestamp) {
            $this->resetToRequest();
            Flux::toast(__('general.otp_invalid'));

            return;
        }

        $this->validate([
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ], attributes: [
            'password' => __('general.new_password'),
            'password_confirmation' => __('general.password_confirmation'),
        ]);

        $user->update([
            'password' => $this->password,
        ]);

        session()->forget([
            'otp.password_reset.user_id',
            'otp.password_reset.channel',
            'otp.password_reset.verified_at',
        ]);

        Flux::toast(__('general.password_reset_success'));

        $this->redirect(route('login'), navigate: true);
    }

    protected function resetUser(): ?User
    {
        $userId = session('otp.password_reset.user_id');

        return $userId ? User::query()->find($userId) : null;
    }

    protected function resetToRequest(): void
    {
        session()->forget([
            'otp.password_reset.user_id',
            'otp.password_reset.channel',
            'otp.password_reset.verified_at',
        ]);

        $this->step = 'request';
        $this->code = '';
        $this->password = '';
        $this->password_confirmation = '';
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.forgot_password') }} - {{ config('app.name') }}</x-slot>

    @if ($step === 'request')
        <form wire:submit="send" class="space-y-4">
            <div class="space-y-2 text-center">
                <flux:heading size="lg">{{ __('general.forgot_password') }}</flux:heading>
                <flux:text>{{ __('general.forgot_password_hint') }}</flux:text>
            </div>

            <flux:tab.group>
                <flux:tabs variant="segmented" wire:model.live="identifierType" class="w-full">
                    <flux:tab name="mobile" class="flex-1 justify-center cursor-pointer">
                        {{ __('general.mobile') }}
                    </flux:tab>
                    <flux:tab name="email" class="flex-1 justify-center cursor-pointer">
                        {{ __('general.email') }}
                    </flux:tab>
                </flux:tabs>

                <flux:tab.panel name="mobile">
                    <flux:field>
                        <flux:label>{{ __('general.mobile') }}</flux:label>
                        <flux:input type="text" wire:model="mobile" icon="phone" placeholder="09123456789" />
                        <flux:error name="mobile" />
                    </flux:field>
                </flux:tab.panel>

                <flux:tab.panel name="email">
                    <flux:field>
                        <flux:label>{{ __('general.email') }}</flux:label>
                        <flux:input type="email" wire:model="email" icon="envelope" placeholder="your@email.com" />
                        <flux:error name="email" />
                    </flux:field>
                </flux:tab.panel>
            </flux:tab.group>

            <flux:error name="otp" />

            <flux:button type="submit" variant="primary" color="teal" class="w-full">
                {{ __('general.send_otp') }}
            </flux:button>
        </form>
    @elseif ($step === 'verify')
        <form wire:submit="verify" class="space-y-8">
            <div class="max-w-64 mx-auto space-y-2">
                <flux:heading size="lg" class="text-center">{{ __('general.otp_verify_heading') }}</flux:heading>
                <flux:text class="text-center">{{ __('general.otp_verify_hint') }}</flux:text>
            </div>

            <div class="space-y-6">
                <flux:otp wire:model="code" length="6" submit="auto" class="mx-auto" />
                <flux:error name="code" />
                <flux:error name="otp" />
            </div>

            <flux:button type="submit" variant="primary" color="teal" class="w-full">
                {{ __('general.verify_otp') }}
            </flux:button>

            <flux:button type="button" variant="ghost" wire:click="resend" class="w-full">
                {{ __('general.resend_otp') }}
            </flux:button>
        </form>
    @else
        <form wire:submit="resetPassword" class="space-y-4">
            <div class="space-y-2 text-center">
                <flux:heading size="lg">{{ __('general.reset_password') }}</flux:heading>
                <flux:text>{{ __('general.reset_password_hint') }}</flux:text>
            </div>

            <flux:field>
                <flux:label>{{ __('general.new_password') }}</flux:label>
                <flux:input type="password" wire:model="password" icon="lock" placeholder="••••••••" viewable />
                <flux:error name="password" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('general.password_confirmation') }}</flux:label>
                <flux:input type="password" wire:model="password_confirmation" icon="lock" placeholder="••••••••" viewable />
                <flux:error name="password_confirmation" />
            </flux:field>

            <flux:button type="submit" variant="primary" color="orange" class="w-full">
                {{ __('general.save') }}
            </flux:button>
        </form>
    @endif

    <div class="mt-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
        <flux:link href="{{ route('login') }}" wire:navigate class="font-medium">
            {{ __('general.back_to_login') }}
        </flux:link>
    </div>
</div>
