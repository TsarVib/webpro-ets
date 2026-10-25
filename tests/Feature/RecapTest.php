<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the recap summarizes the selected month by category', function () {
    $account = Account::factory()->create();
    $salary = Category::factory()->create([
        'accounts_id' => $account->id,
        'name' => 'Salary',
        'type' => 'income',
    ]);
    $food = Category::factory()->create([
        'accounts_id' => $account->id,
        'name' => 'Food',
        'type' => 'expense',
    ]);

    Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $salary->id,
        'type' => 'income',
        'amount' => 5000000,
        'date' => '2026-10-05',
    ]);
    Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $food->id,
        'type' => 'expense',
        'amount' => 350000,
        'date' => '2026-10-08',
    ]);
    Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $salary->id,
        'type' => 'income',
        'amount' => 900000,
        'date' => '2026-09-30',
    ]);

    $this->actingAs($account)
        ->get(route('recap', ['month' => '2026-10']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Recap')
            ->where('month', '2026-10')
            ->where('summary.income', 5000000)
            ->where('summary.expense', 350000)
            ->where('summary.net', 4650000)
            ->where('category_totals.income.0.category', 'Salary')
            ->where('category_totals.income.0.total', 5000000)
            ->where('category_totals.expense.0.category', 'Food')
            ->where('category_totals.expense.0.total', 350000));
});

test('the recap only includes transactions for the authenticated account', function () {
    $account = Account::factory()->create();
    $otherAccount = Account::factory()->create();
    $category = Category::factory()->create([
        'accounts_id' => $otherAccount->id,
        'type' => 'income',
    ]);

    Transaction::factory()->create([
        'accounts_id' => $otherAccount->id,
        'categories_id' => $category->id,
        'type' => 'income',
        'amount' => 999999,
        'date' => '2026-10-10',
    ]);

    $this->actingAs($account)
        ->get(route('recap', ['month' => '2026-10']))
        ->assertInertia(fn ($page) => $page
            ->where('summary.income', 0)
            ->where('summary.expense', 0)
            ->has('category_totals.income', 0)
            ->has('category_totals.expense', 0));
});

test('the recap supports a range of months', function () {
    $account = Account::factory()->create();
    $category = Category::factory()->create([
        'accounts_id' => $account->id,
        'type' => 'income',
    ]);

    Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $category->id,
        'type' => 'income',
        'amount' => 100000,
        'date' => '2026-01-15',
    ]);
    Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $category->id,
        'type' => 'income',
        'amount' => 250000,
        'date' => '2026-03-15',
    ]);

    $this->actingAs($account)
        ->get(route('recap', [
            'mode' => 'range',
            'from' => '2026-01',
            'to' => '2026-03',
        ]))
        ->assertInertia(fn ($page) => $page
            ->where('mode', 'range')
            ->where('period_label', 'January 2026 to March 2026')
            ->where('summary.income', 350000));
});

test('the recap supports a full year report', function () {
    $account = Account::factory()->create();
    $category = Category::factory()->create([
        'accounts_id' => $account->id,
        'type' => 'expense',
    ]);

    Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $category->id,
        'type' => 'expense',
        'amount' => 400000,
        'date' => '2026-02-15',
    ]);
    Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $category->id,
        'type' => 'expense',
        'amount' => 600000,
        'date' => '2026-12-15',
    ]);

    $this->actingAs($account)
        ->get(route('recap', ['mode' => 'year', 'year' => '2026']))
        ->assertInertia(fn ($page) => $page
            ->where('mode', 'year')
            ->where('period_label', '2026')
            ->where('summary.expense', 1000000));
});

test('the recap exports the selected period as csv, html, and pdf', function () {
    $account = Account::factory()->create();
    $category = Category::factory()->create([
        'accounts_id' => $account->id,
        'type' => 'income',
        'name' => 'Consulting',
    ]);

    Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $category->id,
        'type' => 'income',
        'amount' => 1250000,
        'date' => '2026-04-10',
        'notes' => 'April project',
    ]);

    $query = [
        'mode' => 'range',
        'from' => '2026-04',
        'to' => '2026-04',
    ];

    $this->actingAs($account)
        ->get(route('recap.csv', $query))
        ->assertDownload('recap-april-2026-to-april-2026.csv')
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $this->actingAs($account)
        ->get(route('recap.report', $query))
        ->assertOk()
        ->assertSee('Financial recap')
        ->assertSee('April project');

    $this->actingAs($account)
        ->get(route('recap.pdf', $query))
        ->assertDownload('recap-april-2026-to-april-2026.pdf');
});
