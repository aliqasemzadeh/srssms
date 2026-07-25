<?php

namespace App\Models\Finance;

use App\Enums\DepositStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'wallet_id',
    'created_by',
    'amount',
    'fee',
    'tax',
    'method',
    'tracking_code',
    'status',
    'ip_address',
    'meta',
    'admin_note',
])]
class Deposit extends Model
{
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'fee' => 'decimal:8',
            'tax' => 'decimal:8',
            'amount_settled' => 'decimal:8',
            'status' => DepositStatusEnum::class,
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class)->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'reference');
    }

    public function isPending(): bool
    {
        return $this->status === DepositStatusEnum::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === DepositStatusEnum::Approved;
    }

    public function isCompleted(): bool
    {
        return $this->status === DepositStatusEnum::Completed;
    }

    public function isRejected(): bool
    {
        return $this->status === DepositStatusEnum::Rejected;
    }

    public function settledAmount(): string
    {
        if ($this->amount_settled !== null) {
            return (string) $this->amount_settled;
        }

        return bcsub(bcsub((string) $this->amount, (string) $this->fee, 8), (string) $this->tax, 8);
    }
}
