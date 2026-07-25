<?php

namespace App\Jobs\Phonebook;

use App\Models\Phonebook\Note;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchDueNoteRemindersJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Note::query()
            ->dueForReminder()
            ->select('user_id')
            ->distinct()
            ->pluck('user_id')
            ->each(fn (int $userId) => SendUserNoteRemindersJob::dispatch($userId));
    }
}
