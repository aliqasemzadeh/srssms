<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'currency_id',
    'balance',
    'locked_balance',
    'is_active',
])]
class Wallet extends Model
{
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => 'decimal:8',
            'locked_balance' => 'decimal:8',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class)->withTrashed();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function getAvailableBalanceAttribute(): string
    {
        return bcsub((string) $this->balance, (string) $this->locked_balance, 8);
    }

    /**
     * Recalculate balance from all wallet transactions (credits − debits).
     */
    public function recalculateBalance(): string
    {
        $balance = '0';

        $this->transactions()
            ->orderBy('id')
            ->select(['id', 'type', 'amount'])
            ->cursor()
            ->each(function (Transaction $transaction) use (&$balance) {
                if ($transaction->type === Transaction::TYPE_CREDIT) {
                    $balance = bcadd($balance, (string) $transaction->amount, 8);
                } else {
                    $balance = bcsub($balance, (string) $transaction->amount, 8);
                }
            });

        $this->forceFill(['balance' => $balance])->saveQuietly();

        return $balance;
    }
}
