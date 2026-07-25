<?php

namespace App\Services\Sms;

use App\Contracts\SmsSender;
use Illuminate\Support\Facades\Log;

class LogSmsSender implements SmsSender
{
    public function send(string $mobile, string $message): void
    {
        Log::channel(config('services.sms.log_channel', 'stack'))->info('SMS sent', [
            'mobile' => $mobile,
            'message' => $message,
        ]);
    }
}
