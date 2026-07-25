<?php

namespace App\Models\Sms;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'name',
    'token',
    'allowed_ips',
    'is_active',
    'last_used_at',
])]
class Token extends Model
{
    protected $table = 'sms_tokens';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'allowed_ips' => 'array',
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function allowsIp(?string $ip): bool
    {
        $allowed = collect($this->allowed_ips ?? [])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values();

        if ($allowed->isEmpty()) {
            return true;
        }

        if ($ip === null || $ip === '') {
            return false;
        }

        return $allowed->contains($ip);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TokenLog::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
