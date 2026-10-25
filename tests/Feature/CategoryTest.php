<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an account can create and update its categories', function () {
    $account = Account::factory()->create();

    $this->actingAs($account)
        ->post(route('categories.store'), [
            'name' => 'Rent',
            'type' => 'expense',
        ])
        ->assertRedirect();

    $category = Category::query()
        ->where('accounts_id', $account->id)
        ->where('name', 'Rent')
        ->firstOrFail();

    $this->actingAs($account)
        ->patch(route('categories.update', $category), [
            'name' => 'Monthly rent',
            'type' => 'expense',
        ])
        ->assertRedirect();

    expect($category->fresh()->name)->toBe('Monthly rent');
});

test('category names must be unique for an account and type', function () {
    $account = Account::factory()->create();
    Category::factory()->create([
        'accounts_id' => $account->id,
        'name' => 'Bills',
        'type' => 'expense',
    ]);

    $this->actingAs($account)
        ->post(route('categories.store'), [
            'name' => 'Bills',
            'type' => 'expense',
        ])
        ->assertSessionHasErrors('name');
});

test('an account cannot update or delete another accounts category', function () {
    $account = Account::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($account)
        ->patch(route('categories.update', $category), [
            'name' => 'Updated',
            'type' => $category->type,
        ])
        ->assertNotFound();

    $this->actingAs($account)
        ->delete(route('categories.destroy', $category))
        ->assertNotFound();
});

test('a category with transactions cannot be deleted', function () {
    $account = Account::factory()->create();
    $category = Category::factory()->create([
        'accounts_id' => $account->id,
        'type' => 'expense',
    ]);
    Transaction::factory()->create([
        'accounts_id' => $account->id,
        'categories_id' => $category->id,
        'type' => 'expense',
    ]);

    $this->actingAs($account)
        ->delete(route('categories.destroy', $category))
        ->assertSessionHasErrors('category');

    expect($category->fresh()->deleted_at)->toBeNull();
});

test('an account can delete an unused category', function () {
    $account = Account::factory()->create();
    $category = Category::factory()->create([
        'accounts_id' => $account->id,
    ]);

    $this->actingAs($account)
        ->delete(route('categories.destroy', $category))
        ->assertRedirect();

    expect($category->fresh()->deleted_at)->not->toBeNull();
});
