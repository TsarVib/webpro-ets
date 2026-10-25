<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('deleting an income redirects to the income page', function () {
    $account = Account::factory()->create();
    $category = Category::factory()->create([
        'accounts_id' => $account->id,
        'type' => 'income',
    ]);
    $transaction = Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $category->id,
        'type' => 'income',
    ]);

    $this->actingAs($account)
        ->delete(route('transactions.destroy', $transaction))
        ->assertRedirect(route('income'));

    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
});

test('deleting an expense redirects to the expense page', function () {
    $account = Account::factory()->create();
    $category = Category::factory()->create([
        'accounts_id' => $account->id,
        'type' => 'expense',
    ]);
    $transaction = Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $category->id,
        'type' => 'expense',
    ]);

    $this->actingAs($account)
        ->delete(route('transactions.destroy', $transaction))
        ->assertRedirect(route('expense'));

    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
});
