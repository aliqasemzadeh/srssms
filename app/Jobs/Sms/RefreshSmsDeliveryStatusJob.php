<?php

namespace App\Jobs\Sms;

use App\Enums\Sms\SmsMessageStatusEnum;
use App\Models\Sms\Message;
use App\Services\Sms\SmsDeliveryStatusSyncer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshSmsDeliveryStatusJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(
        public int $messageId,
    ) {}

    public function handle(SmsDeliveryStatusSyncer $syncer): void
    {
        $message = Message::query()
            ->with(['gateway.provider', 'recipients'])
            ->find($this->messageId);

        if (! $message) {
            return;
        }

        $recipients = $message->recipients;

        if ($recipients->isNotEmpty() && $recipients->every(
            fn ($recipient) => in_array($recipient->status, [
                SmsMessageStatusEnum::Delivered,
                SmsMessageStatusEnum::Failed,
            ], true)
        )) {
            return;
        }

        $syncer->sync($message);
    }
}
