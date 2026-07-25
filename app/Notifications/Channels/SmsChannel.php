<?php

namespace App\Notifications\Channels;

use App\Contracts\SmsSender;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    public function __construct(protected SmsSender $sms) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $mobile = $notifiable->routeNotificationFor('sms', $notification)
            ?? $notifiable->mobile
            ?? null;

        if (! $mobile) {
            return;
        }

        $this->sms->send($mobile, $notification->toSms($notifiable));
    }
}
