<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an account can record and view income and expense entries', function () {
    $account = Account::factory()->create();
    $incomeCategory = Category::factory()->create([
        'accounts_id' => $account->id,
        'type' => 'income',
        'name' => 'Salary',
    ]);
    $expenseCategory = Category::factory()->create([
        'accounts_id' => $account->id,
        'type' => 'expense',
        'name' => 'Food',
    ]);

    $this->actingAs($account)
        ->post(route('transactions.store'), [
            'type' => 'income',
            'category_id' => $incomeCategory->id,
            'amount' => 5000000,
            'date' => '2026-10-25',
            'time' => '09:30',
            'notes' => 'October salary',
        ])
        ->assertRedirect();

    $this->actingAs($account)
        ->post(route('transactions.store'), [
            'type' => 'expense',
            'category_id' => $expenseCategory->id,
            'amount' => 75000,
            'date' => '2026-10-25',
            'time' => '12:15',
            'notes' => 'Lunch',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('transactions', [
        'accounts_id' => $account->id,
        'type' => 'income',
        'amount' => 5000000,
        'categories_id' => $incomeCategory->id,
    ]);

    $this->actingAs($account)
        ->get(route('income'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Income')
            ->where('total', 5000000)
            ->where('transactions.total', 1)
            ->where('transactions.data.0.notes', 'October salary'));

    $this->actingAs($account)
        ->get(route('expense'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Expense')
            ->where('total', 75000)
            ->where('transactions.total', 1)
            ->where('transactions.data.0.notes', 'Lunch'));
});

test('an account cannot use another account category or edit another account transaction', function () {
    $account = Account::factory()->create();
    $otherAccount = Account::factory()->create();
    $category = Category::factory()->create([
        'accounts_id' => $otherAccount->id,
        'type' => 'expense',
    ]);
    $transaction = Transaction::factory()->create([
        'accounts_id' => $otherAccount->id,
        'categories_id' => $category->id,
        'type' => 'expense',
    ]);

    $this->actingAs($account)
        ->post(route('transactions.store'), [
            'type' => 'expense',
            'category_id' => $category->id,
            'amount' => 100,
            'date' => '2026-10-25',
        ])
        ->assertSessionHasErrors('category_id');

    $this->actingAs($account)
        ->patch(route('transactions.update', $transaction), [
            'type' => 'expense',
            'category_id' => $category->id,
            'amount' => 200,
            'date' => '2026-10-25',
        ])
        ->assertForbidden();
});
