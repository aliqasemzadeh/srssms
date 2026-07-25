<?php

namespace App\Models\Support;

use App\Enums\Support\TicketPriorityEnum;
use App\Enums\Support\TicketStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'title',
    'status',
    'priority',
    'last_replied_at',
])]
class Ticket extends Model
{
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TicketStatusEnum::class,
            'priority' => TicketPriorityEnum::class,
            'last_replied_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->orderByDesc('created_at');
    }

    public function scopeOwnedBy(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('user_id', $userId);
    }

    public function scopeNeedsAttention(Builder $query): Builder
    {
        return $query->whereIn(
            'status',
            array_map(fn (TicketStatusEnum $status) => $status->value, TicketStatusEnum::attentionCases()),
        );
    }

    public function isClosed(): bool
    {
        return $this->status === TicketStatusEnum::Closed;
    }

    public function isOwnedBy(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->user_id === $userId;
    }
}
