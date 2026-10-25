<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'type' => 'expense',
            'amount' => fake()->numberBetween(1, 1_000_000),
            'notes' => fake()->sentence(),
            'date' => now()->toDateString(),
            'time' => now()->format('H:i:s'),
            'accounts_id' => Account::factory(),
            'categories_id' => function (array $attributes): int {
                return Category::factory()->create([
                    'accounts_id' => $attributes['accounts_id'],
                    'type' => $attributes['type'],
                ])->id;
            },
        ];
    }
}
