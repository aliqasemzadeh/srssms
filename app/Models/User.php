<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Finance\Deposit;
use App\Models\Finance\Wallet;
use App\Models\Finance\Withdrawal;
use App\Models\Phonebook\Contact as PhonebookContact;
use App\Models\Phonebook\Group as PhonebookGroup;
use App\Models\Phonebook\Note as PhonebookNote;
use App\Models\Sms\Gateway;
use App\Models\Sms\Message;
use App\Models\Sms\Token;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\OneTimePasswords\Models\Concerns\HasOneTimePasswords;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'first_name',
    'last_name',
    'mobile',
    'email',
    'username',
    'password',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasOneTimePasswords, HasRoles, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function routeNotificationForSms(): ?string
    {
        return $this->mobile;
    }

    /**
     * Soft-deleted wallets are excluded by default.
     */
    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(UserAccount::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function smsGateways(): BelongsToMany
    {
        return $this->belongsToMany(Gateway::class, 'sms_gateway_user')
            ->withTimestamps();
    }

    public function smsMessages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function smsTokens(): HasMany
    {
        return $this->hasMany(Token::class);
    }

    public function phonebookContacts(): HasMany
    {
        return $this->hasMany(PhonebookContact::class);
    }

    public function phonebookGroups(): HasMany
    {
        return $this->hasMany(PhonebookGroup::class);
    }

    public function phonebookNotes(): HasMany
    {
        return $this->hasMany(PhonebookNote::class);
    }
}
