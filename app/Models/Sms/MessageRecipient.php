<?php

namespace App\Models\Sms;

use App\Enums\Sms\SmsMessageStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'message_id',
    'mobile',
    'status',
    'reference_id',
    'error',
    'delivered_at',
])]
class MessageRecipient extends Model
{
    protected $table = 'sms_message_recipients';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SmsMessageStatusEnum::class,
            'delivered_at' => 'datetime',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
