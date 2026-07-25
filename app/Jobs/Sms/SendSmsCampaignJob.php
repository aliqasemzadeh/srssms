<?php

namespace App\Jobs\Sms;

use App\Enums\Sms\SmsMessageStatusEnum;
use App\Models\Sms\Message;
use App\Services\Sms\SmsManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendSmsCampaignJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $messageId,
    ) {}

    public function handle(SmsManager $manager): void
    {
        $message = Message::query()
            ->with(['gateway.provider', 'recipients'])
            ->find($this->messageId);

        if (! $message || ! $message->gateway) {
            return;
        }

        if (! in_array($message->status, [SmsMessageStatusEnum::Queued, SmsMessageStatusEnum::Pending], true)) {
            return;
        }

        $pendingRecipients = $message->recipients->filter(
            fn ($recipient) => in_array($recipient->status, [SmsMessageStatusEnum::Queued, SmsMessageStatusEnum::Pending], true)
        );

        $mobiles = $pendingRecipients->pluck('mobile')->filter()->unique()->values()->all();

        if ($mobiles === []) {
            $this->syncMessageStatus($message);

            return;
        }

        try {
            $result = $manager->driverFor($message->gateway)->send($message->gateway, $mobiles, $message->body);
        } catch (Throwable $e) {
            Log::error('SMS campaign send failed', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            $message->recipients()
                ->whereIn('status', [SmsMessageStatusEnum::Queued, SmsMessageStatusEnum::Pending])
                ->update([
                    'status' => SmsMessageStatusEnum::Failed,
                    'error' => $e->getMessage(),
                ]);

            $this->syncMessageStatus($message->fresh(['recipients']));

            throw $e;
        }

        $message->provider_payload = is_array($result->raw) ? $result->raw : ['raw' => $result->raw];

        foreach ($result->recipients as $recipientResult) {
            $recipient = $message->recipients
                ->first(fn ($row) => $row->mobile === $recipientResult['mobile']);

            if (! $recipient) {
                continue;
            }

            $status = SmsMessageStatusEnum::tryFrom($recipientResult['status'])
                ?? SmsMessageStatusEnum::Failed;

            $recipient->update([
                'status' => $status,
                'reference_id' => $recipientResult['reference_id'] ?? null,
                'error' => $recipientResult['error'] ?? null,
                'delivered_at' => $status === SmsMessageStatusEnum::Delivered ? now() : null,
            ]);
        }

        $message->sent_at = now();
        $message->save();

        $this->syncMessageStatus($message->fresh(['recipients']));
    }

    protected function syncMessageStatus(Message $message): void
    {
        $recipients = $message->recipients;

        if ($recipients->isEmpty()) {
            $message->update(['status' => SmsMessageStatusEnum::Failed]);

            return;
        }

        $hasPending = $recipients->contains(
            fn ($recipient) => in_array($recipient->status, [SmsMessageStatusEnum::Queued, SmsMessageStatusEnum::Pending], true)
        );

        if ($hasPending) {
            $message->update(['status' => SmsMessageStatusEnum::Queued]);

            return;
        }

        $allFailed = $recipients->every(
            fn ($recipient) => $recipient->status === SmsMessageStatusEnum::Failed
        );

        $allDelivered = $recipients->every(
            fn ($recipient) => $recipient->status === SmsMessageStatusEnum::Delivered
        );

        $message->update([
            'status' => match (true) {
                $allFailed => SmsMessageStatusEnum::Failed,
                $allDelivered => SmsMessageStatusEnum::Delivered,
                default => SmsMessageStatusEnum::Sent,
            },
        ]);
    }
}
