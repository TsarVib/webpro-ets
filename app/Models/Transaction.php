<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $type
 * @property int $amount
 * @property string|null $notes
 * @property string $date
 * @property string|null $time
 * @property int $accounts_id
 * @property int $categories_id
 * @property Category|null $category
 *
 * @use HasFactory<TransactionFactory>
 */
#[Fillable(['type', 'amount', 'notes', 'date', 'time', 'accounts_id', 'categories_id'])]
class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'accounts_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categories_id');
    }
}
