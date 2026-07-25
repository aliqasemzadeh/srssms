<?php

namespace App\Models\Sms;

use App\Enums\Sms\SmsDirectionEnum;
use App\Enums\Sms\SmsEncodingEnum;
use App\Enums\Sms\SmsMessageStatusEnum;
use App\Models\Finance\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'gateway_id',
    'user_id',
    'direction',
    'number',
    'body',
    'parts_count',
    'sms_rate',
    'cost',
    'encoding',
    'status',
    'reference_id',
    'provider_payload',
    'sent_at',
    'received_at',
])]
class Message extends Model
{
    protected $table = 'sms_messages';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'direction' => SmsDirectionEnum::class,
            'encoding' => SmsEncodingEnum::class,
            'status' => SmsMessageStatusEnum::class,
            'parts_count' => 'integer',
            'sms_rate' => 'integer',
            'cost' => 'integer',
            'provider_payload' => 'array',
            'sent_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(Gateway::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(MessageRecipient::class);
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'reference');
    }
}
