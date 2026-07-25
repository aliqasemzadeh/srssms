<?php

namespace App\Services\Sms\Drivers;

use App\Contracts\Sms\SmsDriver;
use App\Enums\Sms\SmsMessageStatusEnum;
use App\Models\Sms\Gateway;
use App\Services\Sms\SmsSendResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogDriver implements SmsDriver
{
    public function send(Gateway $gateway, array $mobiles, string $text): SmsSendResult
    {
        $recipients = [];

        foreach ($mobiles as $mobile) {
            $referenceId = 'log-'.Str::uuid()->toString();

            Log::channel(config('sms.log_channel', 'stack'))->info('SMS sent via LogDriver', [
                'gateway' => $gateway->number,
                'mobile' => $mobile,
                'message' => $text,
                'reference_id' => $referenceId,
            ]);

            $recipients[] = [
                'mobile' => $mobile,
                'status' => SmsMessageStatusEnum::Sent->value,
                'reference_id' => $referenceId,
                'error' => null,
            ];
        }

        return new SmsSendResult(success: true, recipients: $recipients);
    }
}
