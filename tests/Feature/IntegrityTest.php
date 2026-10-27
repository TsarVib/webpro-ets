<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Database\Seeders\DefaultCategorySeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

function categoryFor(Account $account, string $type, ?string $name = null): Category
{
    return Category::factory()->create([
        'accounts_id' => $account->id,
        'type' => $type,
        'name' => $name ?? fake()->unique()->word(),
    ]);
}

test('registering an account copies the default categories', function () {
    $this->seed(DefaultCategorySeeder::class);

    $this->post(route('register.create'), [
        'username' => 'newbie',
        'full_name' => 'New Bie',
        'email' => 'newbie@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('login'));

    $account = Account::where('username', 'newbie')->firstOrFail();

    expect($account->categories()->count())
        ->toBe(DB::table('default_categories')->count())
        ->toBeGreaterThan(0);
});

test('transaction input is validated', function (array $override, string $field) {
    $account = Account::factory()->create();
    $category = categoryFor($account, 'expense');

    $this->actingAs($account)
        ->post(route('transactions.store'), array_merge([
            'type' => 'expense',
            'category_id' => $category->id,
            'amount' => 1000,
            'date' => '2026-10-25',
            'time' => '10:00',
        ], $override))
        ->assertSessionHasErrors($field);

    expect(Transaction::count())->toBe(0);
})->with([
    'zero amount' => [['amount' => 0], 'amount'],
    'negative amount' => [['amount' => -5], 'amount'],
    'decimal amount' => [['amount' => 10.5], 'amount'],
    'text amount' => [['amount' => 'abc'], 'amount'],
    'unknown type' => [['type' => 'transfer'], 'type'],
    'bad date' => [['date' => '2026-13-40'], 'date'],
    'bad time' => [['time' => '25:99'], 'time'],
    'missing category' => [['category_id' => null], 'category_id'],
]);

test('a transaction cannot use a category of the other type', function () {
    $account = Account::factory()->create();
    $income = categoryFor($account, 'income');

    $this->actingAs($account)
        ->post(route('transactions.store'), [
            'type' => 'expense',
            'category_id' => $income->id,
            'amount' => 1000,
            'date' => '2026-10-25',
        ])
        ->assertSessionHasErrors('category_id');
});

test('a transaction can be edited including its type', function () {
    $account = Account::factory()->create();
    $expense = categoryFor($account, 'expense');
    $income = categoryFor($account, 'income');
    $transaction = Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $expense->id,
        'type' => 'expense',
        'amount' => 100,
    ]);

    $this->actingAs($account)
        ->patch(route('transactions.update', $transaction), [
            'type' => 'income',
            'category_id' => $income->id,
            'amount' => 250,
            'notes' => 'fixed',
            'date' => '2026-10-01',
            'time' => '08:15',
        ])
        ->assertRedirect();

    expect($transaction->fresh())
        ->type->toBe('income')
        ->amount->toBe(250)
        ->categories_id->toBe($income->id)
        ->time->toBe('08:15:00');
});

test('an account cannot delete another accounts transaction', function () {
    $owner = Account::factory()->create();
    $transaction = Transaction::factory()->create(['accounts_id' => $owner->id]);

    $this->actingAs(Account::factory()->create())
        ->delete(route('transactions.destroy', $transaction))
        ->assertForbidden();

    expect(Transaction::find($transaction->id))->not->toBeNull();
});

test('guests cannot write data or export', function () {
    $this->post(route('transactions.store'))->assertRedirect(route('login'));
    $this->post(route('categories.store'))->assertRedirect(route('login'));
    $this->get(route('expense.export'))->assertRedirect(route('login'));
    $this->get(route('recap.pdf'))->assertRedirect(route('login'));
});

test('the transaction list filters by date range, category and keyword', function () {
    $account = Account::factory()->create();
    $food = categoryFor($account, 'expense', 'Food');
    $bills = categoryFor($account, 'expense', 'Bills');
    $make = fn (Category $c, string $date, string $notes) => Transaction::factory()->create([
        'accounts_id' => $account->id, 'categories_id' => $c->id, 'type' => 'expense',
        'amount' => 100, 'date' => $date, 'notes' => $notes,
    ]);
    $make($food, '2026-09-30', 'old lunch');
    $make($food, '2026-10-05', 'lunch with team');
    $make($bills, '2026-10-06', 'electricity');

    $page = fn (array $query) => $this->actingAs($account)->get(route('expense', $query))->assertOk();

    $page(['from' => '2026-10-01', 'to' => '2026-10-31'])
        ->assertInertia(fn ($p) => $p->where('transactions.total', 2)->where('total', 200));
    $page(['category' => $bills->id])
        ->assertInertia(fn ($p) => $p->where('transactions.total', 1)->where('transactions.data.0.notes', 'electricity'));
    $page(['q' => 'lunch'])
        ->assertInertia(fn ($p) => $p->where('transactions.total', 2)->where('filters.q', 'lunch'));

    $this->actingAs($account)
        ->get(route('expense', ['from' => '2026-10-31', 'to' => '2026-10-01']))
        ->assertSessionHasErrors('to');
});

test('the transaction list is paginated newest first', function () {
    $account = Account::factory()->create();
    $category = categoryFor($account, 'income');

    foreach (range(1, 20) as $day) {
        Transaction::factory()->create([
            'accounts_id' => $account->id, 'categories_id' => $category->id, 'type' => 'income',
            'date' => sprintf('2026-10-%02d', $day), 'time' => '12:00:00',
        ]);
    }

    $this->actingAs($account)->get(route('income'))
        ->assertInertia(fn ($p) => $p
            ->where('transactions.total', 20)
            ->has('transactions.data', 15)
            ->where('transactions.data.0.date', '2026-10-20'));
});

test('csv export contains only the filtered rows of the account', function () {
    $account = Account::factory()->create();
    $category = categoryFor($account, 'expense', 'Food');
    Transaction::factory()->create([
        'accounts_id' => $account->id, 'categories_id' => $category->id, 'type' => 'expense',
        'amount' => 15000, 'date' => '2026-10-05', 'time' => '12:00:00', 'notes' => 'lunch',
    ]);
    Transaction::factory()->create([
        'accounts_id' => $account->id, 'categories_id' => $category->id, 'type' => 'expense',
        'amount' => 999, 'date' => '2026-09-05', 'notes' => 'outside range',
    ]);
    Transaction::factory()->create(['notes' => 'someone else']);

    $csv = $this->actingAs($account)
        ->get(route('expense.export', ['from' => '2026-10-01']))
        ->assertOk()
        ->streamedContent();

    $lines = array_values(array_filter(explode("\n", $csv)));

    expect($lines)->toHaveCount(2)
        ->and($lines[0])->toBe('date,time,type,category,amount,notes')
        ->and($lines[1])->toBe('2026-10-05,12:00:00,expense,Food,15000,lunch');
});

test('a soft deleted category is hidden and its name can be reused', function () {
    $account = Account::factory()->create();
    $category = categoryFor($account, 'expense', 'Snacks');

    $this->actingAs($account)->delete(route('categories.destroy', $category))->assertRedirect();

    expect(Category::withTrashed()->find($category->id)->deleted_at)->not->toBeNull();

    $this->actingAs($account)->get(route('expense'))
        ->assertInertia(fn ($p) => $p->where('categories', fn ($c) => ! collect($c)->contains('name', 'Snacks')));

    $this->actingAs($account)
        ->post(route('transactions.store'), [
            'type' => 'expense', 'category_id' => $category->id, 'amount' => 10, 'date' => '2026-10-25',
        ])
        ->assertSessionHasErrors('category_id');

    $this->actingAs($account)
        ->post(route('categories.store'), ['name' => 'Snacks', 'type' => 'expense'])
        ->assertSessionHasNoErrors();
});

test('a category in use cannot switch type and orphan its transactions', function () {
    $account = Account::factory()->create();
    $category = categoryFor($account, 'expense', 'Food');
    Transaction::factory()->create([
        'accounts_id' => $account->id, 'categories_id' => $category->id, 'type' => 'expense',
    ]);

    $this->actingAs($account)
        ->patch(route('categories.update', $category), ['name' => 'Food', 'type' => 'income'])
        ->assertSessionHasErrors('type');

    expect($category->fresh()->type)->toBe('expense');

    $this->actingAs($account)
        ->patch(route('categories.update', $category), ['name' => 'Groceries', 'type' => 'expense'])
        ->assertSessionHasNoErrors();
});

test('the database rejects invalid rows even without the app', function () {
    $account = Account::factory()->create();
    $category = categoryFor($account, 'expense');

    expect(fn () => DB::table('transactions')->insert([
        'type' => 'expense', 'amount' => 0, 'date' => '2026-10-25',
        'accounts_id' => $account->id, 'categories_id' => $category->id,
    ]))->toThrow(QueryException::class);

    $other = Account::factory()->create();

    expect(fn () => DB::table('transactions')->insert([
        'type' => 'expense', 'amount' => 10, 'date' => '2026-10-25',
        'accounts_id' => $other->id, 'categories_id' => $category->id,
    ]))->toThrow(QueryException::class);
});

test('deleting an account removes its categories and transactions', function () {
    $transaction = Transaction::factory()->create();
    $account = $transaction->account;

    $account->delete();

    expect(Transaction::count())->toBe(0)
        ->and(Category::withTrashed()->where('accounts_id', $account->id)->count())->toBe(0);
});

test('the recap shows an empty state with no data', function () {
    $this->actingAs(Account::factory()->create())
        ->get(route('recap', ['month' => '2020-01']))
        ->assertOk()
        ->assertInertia(fn ($p) => $p
            ->where('summary.income', 0)
            ->where('summary.expense', 0)
            ->where('summary.net', 0));
});
