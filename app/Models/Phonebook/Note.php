<?php

namespace App\Models\Phonebook;

use App\Enums\Phonebook\ContactNoteStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'contact_id',
    'body',
    'status',
    'remind_at',
    'reminded_at',
])]
class Note extends Model
{
    protected $table = 'phonebook_notes';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ContactNoteStatusEnum::class,
            'remind_at' => 'date',
            'reminded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function scopeDueForReminder(Builder $query): Builder
    {
        return $query
            ->whereNotNull('remind_at')
            ->whereNull('reminded_at')
            ->whereDate('remind_at', '<=', now()->toDateString());
    }

    public function scopeOwnedBy(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('user_id', $userId);
    }
}
