<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the dashboard shows account balance and current month totals', function () {
    $account = Account::factory()->create(['initial_balance' => 1000000]);
    $incomeCategory = Category::factory()->create([
        'accounts_id' => $account->id,
        'type' => 'income',
    ]);
    $expenseCategory = Category::factory()->create([
        'accounts_id' => $account->id,
        'type' => 'expense',
    ]);

    Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $incomeCategory->id,
        'type' => 'income',
        'amount' => 500000,
        'date' => now()->toDateString(),
    ]);
    Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $expenseCategory->id,
        'type' => 'expense',
        'amount' => 125000,
        'date' => now()->toDateString(),
    ]);

    $this->actingAs($account)
        ->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Home')
            ->where('summary.balance', 1375000)
            ->where('summary.month_income', 500000)
            ->where('summary.month_expense', 125000)
            ->where('recent_transactions.total', 2)
            ->has('recent_transactions.data', 2));
});

test('the dashboard excludes transactions from another month in month totals', function () {
    $account = Account::factory()->create();
    $category = Category::factory()->create([
        'accounts_id' => $account->id,
        'type' => 'income',
    ]);

    Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $category->id,
        'type' => 'income',
        'amount' => 900000,
        'date' => now()->subMonth()->toDateString(),
    ]);

    $this->actingAs($account)
        ->get(route('home'))
        ->assertInertia(fn ($page) => $page
            ->where('summary.balance', 900000)
            ->where('summary.month_income', 0)
            ->where('summary.month_expense', 0));
});

test('the dashboard filters transactions and totals by the selected filters', function () {
    $account = Account::factory()->create();
    $incomeCategory = Category::factory()->create([
        'accounts_id' => $account->id,
        'type' => 'income',
    ]);
    $expenseCategory = Category::factory()->create([
        'accounts_id' => $account->id,
        'type' => 'expense',
    ]);

    Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $incomeCategory->id,
        'type' => 'income',
        'amount' => 800000,
        'notes' => 'Salary payment',
    ]);
    Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $expenseCategory->id,
        'type' => 'expense',
        'amount' => 100000,
        'notes' => 'Groceries',
    ]);

    $this->actingAs($account)
        ->get(route('home', ['type' => 'income', 'q' => 'salary']))
        ->assertInertia(fn ($page) => $page
            ->where('summary.balance', 800000)
            ->where('summary.month_income', 800000)
            ->where('summary.month_expense', 0)
            ->where('recent_transactions.total', 1)
            ->has('recent_transactions.data', 1)
            ->where('recent_transactions.data.0.notes', 'Salary payment'));
});
