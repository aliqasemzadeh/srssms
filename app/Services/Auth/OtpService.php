<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Notifications\Auth\OneTimePasswordNotification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OtpService
{
    public const PURPOSE_PASSWORD_RESET = 'password_reset';

    public const PURPOSE_LOGIN = 'login';

    public const SEND_DECAY_SECONDS = 120;

    /**
     * @param  'mail'|'sms'  $channel
     */
    public function send(User $user, string $channel, string $purpose): void
    {
        $this->ensureCanSend($user, $purpose);

        $expiresInMinutes = (int) config('one-time-passwords.default_expires_in_minutes', 5);
        $oneTimePassword = $user->createOneTimePassword($expiresInMinutes);

        $user->notify(new OneTimePasswordNotification($oneTimePassword, $channel));

        RateLimiter::hit($this->rateLimitKey($user, $purpose), self::SEND_DECAY_SECONDS);
    }

    public function remainingSeconds(User $user, string $purpose): int
    {
        return RateLimiter::availableIn($this->rateLimitKey($user, $purpose));
    }

    public function canSend(User $user, string $purpose): bool
    {
        return ! RateLimiter::tooManyAttempts($this->rateLimitKey($user, $purpose), 1);
    }

    protected function ensureCanSend(User $user, string $purpose): void
    {
        if ($this->canSend($user, $purpose)) {
            return;
        }

        throw ValidationException::withMessages([
            'otp' => __('general.otp_throttle', [
                'seconds' => $this->remainingSeconds($user, $purpose),
            ]),
        ]);
    }

    protected function rateLimitKey(User $user, string $purpose): string
    {
        return "otp-send:{$purpose}:{$user->id}";
    }
}
