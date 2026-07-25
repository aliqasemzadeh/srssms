<?php

namespace App\Models\Phonebook;

use App\Enums\Phonebook\ContactGenderEnum;
use App\Enums\Phonebook\ContactPersonTypeEnum;
use App\Models\Sms\MessageRecipient;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Tags\HasTags;

#[Fillable([
    'user_id',
    'first_name',
    'last_name',
    'mobile',
    'company',
    'gender',
    'birth_date',
    'marriage_date',
    'address',
    'postal_code',
    'national_code',
    'economic_code',
    'person_type',
])]
class Contact extends Model
{
    use HasTags;
    use SoftDeletes;

    protected $table = 'phonebook_contacts';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => ContactGenderEnum::class,
            'person_type' => ContactPersonTypeEnum::class,
            'birth_date' => 'date',
            'marriage_date' => 'date',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'phonebook_contact_group', 'contact_id', 'group_id')
            ->withTimestamps();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function messageRecipients(): HasMany
    {
        return $this->hasMany(MessageRecipient::class, 'contact_id');
    }

    public function scopeOwnedBy(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('user_id', $userId);
    }

    public static function tagTypeFor(User|int $user): string
    {
        $userId = $user instanceof User ? $user->id : $user;

        return 'user_'.$userId;
    }
}
