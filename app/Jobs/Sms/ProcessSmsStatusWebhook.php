<?php

namespace App\Jobs\Sms;

use App\Enums\Sms\SmsMessageStatusEnum;
use App\Models\Sms\Message;
use App\Models\Sms\MessageRecipient;
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

    public function handle(): void
    {
        $referenceId = isset($this->payload['reference_id'])
            ? (string) $this->payload['reference_id']
            : null;
        $number = isset($this->payload['number']) ? (string) $this->payload['number'] : null;
        $status = $this->mapStatus($this->payload['status'] ?? null);

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

            $this->syncMessageStatus($recipient->message);

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

    protected function syncMessageStatus(?Message $message): void
    {
        if (! $message) {
            return;
        }

        $statuses = $message->recipients()->pluck('status')->map(
            fn ($status) => $status instanceof SmsMessageStatusEnum ? $status : SmsMessageStatusEnum::tryFrom((string) $status)
        )->filter();

        if ($statuses->isEmpty()) {
            return;
        }

        if ($statuses->every(fn (SmsMessageStatusEnum $status) => $status === SmsMessageStatusEnum::Delivered)) {
            $message->update(['status' => SmsMessageStatusEnum::Delivered]);

            return;
        }

        if ($statuses->every(fn (SmsMessageStatusEnum $status) => $status === SmsMessageStatusEnum::Failed)) {
            $message->update(['status' => SmsMessageStatusEnum::Failed]);

            return;
        }

        if ($statuses->contains(SmsMessageStatusEnum::Sent) || $statuses->contains(SmsMessageStatusEnum::Delivered)) {
            $message->update(['status' => SmsMessageStatusEnum::Sent]);
        }
    }

    protected function mapStatus(mixed $status): SmsMessageStatusEnum
    {
        $value = is_numeric($status) ? (int) $status : strtolower((string) $status);

        return match ($value) {
            1, '1', 'sent', 'success' => SmsMessageStatusEnum::Sent,
            2, '2', 'delivered', 'deliver' => SmsMessageStatusEnum::Delivered,
            3, '3', 'failed', 'error', 'undelivered' => SmsMessageStatusEnum::Failed,
            default => SmsMessageStatusEnum::Sent,
        };
    }
}
