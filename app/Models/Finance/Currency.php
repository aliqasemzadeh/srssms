<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'symbol',
    'name',
    'logo',
    'type',
    'decimals',
    'meta',
    'is_active',
])]
class Currency extends Model
{
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_active' => 'boolean',
            'decimals' => 'integer',
        ];
    }
}
