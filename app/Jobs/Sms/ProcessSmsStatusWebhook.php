<?php

namespace App\Jobs\Sms;

use App\Enums\Sms\SmsMessageStatusEnum;
use App\Models\Sms\Message;
use App\Models\Sms\MessageRecipient;
use App\Services\Sms\SmsDeliveryStatusSyncer;
use App\Services\Sms\SmsStatusMapper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessSmsStatusWebhook implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $providerDriver,
        public array $payload,
    ) {}

    public function handle(SmsStatusMapper $mapper, SmsDeliveryStatusSyncer $syncer): void
    {
        $referenceId = isset($this->payload['reference_id'])
            ? (string) $this->payload['reference_id']
            : null;
        $number = isset($this->payload['number']) ? (string) $this->payload['number'] : null;
        $status = $mapper->map($this->payload['status'] ?? null);

        if (! $referenceId) {
            return;
        }

        $recipient = MessageRecipient::query()
            ->where('reference_id', $referenceId)
            ->when($number, fn ($query) => $query->where('mobile', $number))
            ->first();

        if ($recipient) {
            $recipient->update([
                'status' => $status,
                'delivered_at' => $status === SmsMessageStatusEnum::Delivered ? now() : $recipient->delivered_at,
            ]);

            $syncer->syncMessageStatus($recipient->message);

            return;
        }

        $message = Message::query()
            ->where('reference_id', $referenceId)
            ->first();

        if ($message) {
            $message->update([
                'status' => $status,
                'provider_payload' => array_merge($message->provider_payload ?? [], [
                    'status_webhook' => $this->payload,
                ]),
            ]);
        }
    }
}
