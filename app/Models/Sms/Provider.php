<?php

namespace App\Models\Sms;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'driver',
    'credentials',
    'meta',
    'is_active',
])]
class Provider extends Model
{
    use SoftDeletes;

    protected $table = 'sms_providers';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'meta' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function gateways(): HasMany
    {
        return $this->hasMany(Gateway::class);
    }

    public function credential(string $key, mixed $default = null): mixed
    {
        return data_get($this->credentials ?? [], $key, $default);
    }
}
