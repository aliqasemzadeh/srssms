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

    /**
     * Fetch delivery status from the provider.
     *
     * @param  array<int, string>  $referenceIds
     * @return array{
     *     entries: array<int, array{reference_id: ?string, mobile: ?string, status: string, datetime: ?string}>,
     *     raw: mixed
     * }
     */
    public function status(Gateway $gateway, ?string $batchId = null, array $referenceIds = []): array;
}
