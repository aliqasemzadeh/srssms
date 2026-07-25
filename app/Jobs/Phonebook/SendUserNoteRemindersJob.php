<?php

namespace App\Jobs\Phonebook;

use App\Enums\Sms\SmsDirectionEnum;
use App\Enums\Sms\SmsMessageStatusEnum;
use App\Models\Phonebook\Note;
use App\Models\Sms\Gateway;
use App\Models\Sms\Message;
use App\Models\User;
use App\Services\Sms\SmsBillingService;
use App\Services\Sms\SmsPartCounter;
use App\Jobs\Sms\SendSmsCampaignJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SendUserNoteRemindersJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $userId,
    ) {}

    public function handle(SmsBillingService $billing, SmsPartCounter $partCounter): void
    {
        $user = User::query()->find($this->userId);

        if (! $user || blank($user->mobile)) {
            return;
        }

        $gateway = Gateway::query()
            ->with('provider')
            ->where('is_active', true)
            ->usableBy($user)
            ->whereHas('provider', fn ($query) => $query->where('is_active', true))
            ->orderBy('id')
            ->first();

        if (! $gateway) {
            Log::warning('No usable SMS gateway for note reminders', ['user_id' => $user->id]);

            return;
        }

        $notes = Note::query()
            ->with('contact')
            ->ownedBy($user)
            ->dueForReminder()
            ->orderBy('id')
            ->get();

        foreach ($notes as $note) {
            try {
                $this->sendReminder($user, $gateway, $note, $billing, $partCounter);
            } catch (Throwable $e) {
                Log::warning('Note reminder skipped', [
                    'note_id' => $note->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function sendReminder(
        User $user,
        Gateway $gateway,
        Note $note,
        SmsBillingService $billing,
        SmsPartCounter $partCounter,
    ): void {
        $contactName = $note->contact?->full_name ?: __('general.contact');
        $body = __('general.note_reminder_sms_body', [
            'contact' => $contactName,
            'note' => \Illuminate\Support\Str::limit($note->body, 100),
        ]);

        $estimate = $billing->estimate($gateway, $body, 1);

        DB::transaction(function () use ($user, $gateway, $note, $body, $estimate, $billing, $partCounter): void {
            $billing->assertSufficientBalance($user, $estimate['cost']);

            $analysis = $partCounter->analyze($body);

            $message = Message::query()->create([
                'gateway_id' => $gateway->id,
                'user_id' => $user->id,
                'direction' => SmsDirectionEnum::Outbound,
                'number' => $gateway->number,
                'body' => $body,
                'parts_count' => $analysis['parts_count'],
                'sms_rate' => $estimate['sms_rate'],
                'cost' => $estimate['cost'],
                'encoding' => $analysis['encoding'],
                'status' => SmsMessageStatusEnum::Queued,
            ]);

            $message->recipients()->create([
                'contact_id' => $note->contact_id,
                'mobile' => $user->mobile,
                'status' => SmsMessageStatusEnum::Queued,
            ]);

            $billing->debitForMessage($user, $message, $estimate['cost']);

            $note->forceFill(['reminded_at' => now()])->save();

            SendSmsCampaignJob::dispatch($message->id);
        });
    }
}
