<?php

namespace App\Contracts\Sms;

use App\Models\Sms\Gateway;
use App\Services\Sms\SmsSendResult;

interface SmsDriver
{
    /**
     * @param  array<int, string>  $mobiles
     */
    public function send(Gateway $gateway, array $mobiles, string $text): SmsSendResult;
}
