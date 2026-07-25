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

        $gateway = $message->gateway;
        $mobiles = $message->recipients->pluck('mobile')->filter()->unique()->values()->all();

        if ($mobiles === []) {
            $message->update(['status' => SmsMessageStatusEnum::Failed]);

            return;
        }

        try {
            $result = $manager->driverFor($gateway)->send($gateway, $mobiles, $message->body);
        } catch (Throwable $e) {
            Log::error('SMS campaign send failed', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            $message->recipients()->update([
                'status' => SmsMessageStatusEnum::Failed,
                'error' => $e->getMessage(),
            ]);
            $message->update(['status' => SmsMessageStatusEnum::Failed]);

            throw $e;
        }

        $message->provider_payload = is_array($result->raw) ? $result->raw : ['raw' => $result->raw];

        $hasFailure = false;
        $hasSuccess = false;

        foreach ($result->recipients as $recipientResult) {
            $recipient = $message->recipients()
                ->where('mobile', $recipientResult['mobile'])
                ->first();

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

            if ($status === SmsMessageStatusEnum::Failed) {
                $hasFailure = true;
            } else {
                $hasSuccess = true;
            }
        }

        $message->status = match (true) {
            $hasSuccess => SmsMessageStatusEnum::Sent,
            default => SmsMessageStatusEnum::Failed,
        };
        $message->sent_at = now();
        $message->save();
    }
}
