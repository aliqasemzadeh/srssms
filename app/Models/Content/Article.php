<?php

namespace App\Models\Content;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Tags\HasTags;

#[Fillable([
    'title',
    'content',
    'user_id',
])]
class Article extends Model
{
    use HasTags;
    use SoftDeletes;

    public static function tagType(): string
    {
        return 'article';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
