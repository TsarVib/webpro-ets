<?php

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('a guest cannot update the profile', function () {
    $this->patch('/settings', [
        'username' => 'tsar',
        'full_name' => 'Tsar User',
        'email' => 'tsar@example.com',
        'initial_balance' => 0,
    ])->assertRedirect(route('login'));
});

test('an account can update its profile', function () {
    $account = Account::factory()->create();

    $this->actingAs($account)->patch('/settings', [
        'username' => 'tsar',
        'full_name' => 'Tsar User',
        'email' => 'tsar@example.com',
        'initial_balance' => 50000,
    ])->assertRedirect();

    $account->refresh();

    expect($account->username)->toBe('tsar');
    expect($account->full_name)->toBe('Tsar User');
    expect($account->email)->toBe('tsar@example.com');
    expect($account->initial_balance)->toBe(50000);
});

test('a profile update rejects a username or email taken by another account', function () {
    $account = Account::factory()->create();
    Account::factory()->create([
        'username' => 'tsar',
        'email' => 'tsar@example.com',
    ]);

    $this->actingAs($account)->patch('/settings', [
        'username' => 'tsar',
        'full_name' => 'Tsar User',
        'email' => 'tsar@example.com',
        'initial_balance' => 0,
    ])->assertSessionHasErrors(['username', 'email']);
});

test('a profile update accepts unchanged unique values', function () {
    $account = Account::factory()->create();

    $this->actingAs($account)->patch('/settings', [
        'username' => $account->username,
        'full_name' => $account->full_name,
        'email' => $account->email,
        'initial_balance' => 0,
    ])->assertSessionHasNoErrors();
});

test('the initial balance must be a non negative integer', function () {
    $account = Account::factory()->create();

    $this->actingAs($account)->patch('/settings', [
        'username' => $account->username,
        'full_name' => $account->full_name,
        'email' => $account->email,
        'initial_balance' => -5,
    ])->assertSessionHasErrors('initial_balance');
});

test('password can be changed with the current password', function () {
    $account = Account::factory()->create();

    $this->actingAs($account)->put('/settings/password', [
        'current_password' => 'password',
        'password' => 'New-Passw0rd-Long!',
        'password_confirmation' => 'New-Passw0rd-Long!',
    ])->assertRedirect();

    expect(Hash::check('New-Passw0rd-Long!', $account->refresh()->password_hash))->toBeTrue();
});

test('password change fails with a wrong current password', function () {
    $account = Account::factory()->create();

    $this->actingAs($account)->put('/settings/password', [
        'current_password' => 'wrong-password',
        'password' => 'New-Passw0rd-Long!',
        'password_confirmation' => 'New-Passw0rd-Long!',
    ])->assertSessionHasErrors('current_password');

    expect(Hash::check('password', $account->refresh()->password_hash))->toBeTrue();
});

test('password change requires a confirmation', function () {
    $account = Account::factory()->create();

    $this->actingAs($account)->put('/settings/password', [
        'current_password' => 'password',
        'password' => 'New-Passw0rd-Long!',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors('password');
});
