<?php

namespace App\Notifications\Auth;

use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\OneTimePasswords\Models\OneTimePassword;

class OneTimePasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public OneTimePassword $oneTimePassword,
        public string $channel = 'mail',
    ) {}

    public function via(object $notifiable): array
    {
        return $this->channel === 'sms'
            ? [SmsChannel::class]
            : ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $minutes = config('one-time-passwords.default_expires_in_minutes', 5);

        return (new MailMessage)
            ->subject(__('app.otp_mail_subject'))
            ->line(__('app.otp_mail_body', [
                'code' => $this->oneTimePassword->password,
                'minutes' => $minutes,
            ]));
    }

    public function toSms(object $notifiable): string
    {
        return __('app.otp_sms_body', [
            'code' => $this->oneTimePassword->password,
            'minutes' => config('one-time-passwords.default_expires_in_minutes', 5),
        ]);
    }
}
