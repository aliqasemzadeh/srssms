<?php

namespace App\Models\Sms;

use App\Enums\Sms\SmsGatewayAccessTypeEnum;
use App\Enums\Sms\SmsGatewayUsageTypeEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'provider_id',
    'number',
    'title',
    'access_type',
    'usage_type',
    'is_public',
    'settings',
    'is_active',
])]
class Gateway extends Model
{
    use SoftDeletes;

    protected $table = 'sms_gateways';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'access_type' => SmsGatewayAccessTypeEnum::class,
            'usage_type' => SmsGatewayUsageTypeEnum::class,
            'is_public' => 'boolean',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'sms_gateway_user')
            ->withTimestamps();
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeUsableBy(Builder $query, ?User $user = null): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->where('is_public', true);

            if ($user) {
                $query->orWhereHas('users', fn (Builder $users) => $users->where('users.id', $user->id));
            }
        });
    }
}
