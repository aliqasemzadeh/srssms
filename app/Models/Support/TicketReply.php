<?php

namespace App\Models\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'ticket_id',
    'user_id',
    'body',
    'file',
    'file_name',
    'ip_address',
])]
class TicketReply extends Model
{
    use SoftDeletes;

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function hasFile(): bool
    {
        return filled($this->file);
    }

    public function fileUrl(): ?string
    {
        if (! $this->hasFile()) {
            return null;
        }

        return Storage::disk('public')->url($this->file);
    }
}
