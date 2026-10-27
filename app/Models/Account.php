<?php

namespace App\Models;

use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 * @property string $username
 * @property string $full_name
 * @property string $email
 * @property string $password_hash
 * @property int $initial_balance
 */
#[Fillable(['username', 'full_name', 'email', 'password_hash', 'initial_balance'])]
#[Hidden(['password_hash', 'remember_token'])]
class Account extends Authenticatable
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    protected $table = 'accounts';

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'accounts_id');
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'accounts_id');
    }
}
