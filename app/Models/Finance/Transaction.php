<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'wallet_id',
    'type',
    'amount',
    'balance_after',
    'reference_type',
    'reference_id',
    'description',
])]
class Transaction extends Model
{
    public const TYPE_CREDIT = 'credit';

    public const TYPE_DEBIT = 'debit';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'balance_after' => 'decimal:8',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function isCredit(): bool
    {
        return $this->type === self::TYPE_CREDIT;
    }

    public function isDebit(): bool
    {
        return $this->type === self::TYPE_DEBIT;
    }
}
