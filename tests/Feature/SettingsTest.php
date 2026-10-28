<?php

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('secrets of the account are never sent to the page', function () {
    $account = Account::factory()->create(['remember_token' => 'secret-remember-token']);

    $this->actingAs($account)
        ->get(route('settings'))
        ->assertOk()
        ->assertDontSee('secret-remember-token')
        ->assertDontSee($account->password_hash, false)
        ->assertInertia(fn ($page) => $page
            ->where('auth.user.username', $account->username)
            ->missing('auth.user.remember_token')
            ->missing('auth.user.password_hash'));
});

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

test('changing the password signs out other sessions but keeps this one', function () {
    $account = Account::factory()->create();
    $oldHash = $account->password_hash;

    $this->actingAs($account)
        ->put(route('settings.password'), [
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertSessionHasNoErrors();

    $this->get(route('home'))->assertOk();

    // Another device: its session still carries the hash from before the change.
    $this->actingAs($account->fresh())
        ->withSession(['password_hash_web' => $oldHash])
        ->get(route('home'))
        ->assertRedirect(route('login'));
    $this->assertGuest();
});

test('a profile update cannot take another accounts email or username in different capitals', function () {
    Account::factory()->create(['username' => 'taken', 'email' => 'taken@example.com']);
    $account = Account::factory()->create();

    $this->actingAs($account)->patch('/settings', [
        'username' => 'TAKEN',
        'full_name' => $account->full_name,
        'email' => 'TAKEN@example.com',
        'initial_balance' => 0,
    ])->assertSessionHasErrors(['username', 'email']);
});
