<?php

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Cookie;

uses(RefreshDatabase::class);

test('guests are redirected to login from protected pages', function () {
    $this->get('/')->assertRedirect(route('login'));
    $this->get('/income')->assertRedirect(route('login'));
    $this->get('/expense')->assertRedirect(route('login'));
    $this->get('/categories')->assertRedirect(route('login'));
    $this->get('/recap')->assertRedirect(route('login'));
    $this->get('/settings')->assertRedirect(route('login'));
});

test('authenticated users can open every sidebar page', function () {
    $account = Account::factory()->create();

    foreach (['/', '/income', '/expense', '/categories', '/recap', '/settings'] as $path) {
        $this->actingAs($account)->get($path)->assertOk();
    }
});

test('guests can reach the login and register pages', function () {
    $this->get('/login')->assertOk();
    $this->get('/register')->assertOk();
});

test('authenticated users are redirected away from guest pages', function () {
    $account = Account::factory()->create();

    $this->actingAs($account)->get('/login')->assertRedirect(route('home'));
    $this->actingAs($account)->get('/register')->assertRedirect(route('home'));
});

test('registration creates an account without logging in and redirects to login', function () {
    $this->post('/register', [
        'username' => 'tsar',
        'full_name' => 'Tsar User',
        'email' => 'tsar@example.com',
        'password' => 'Sp1ce-R0ute-Long!',
        'password_confirmation' => 'Sp1ce-R0ute-Long!',
    ])->assertRedirect(route('login'));

    $this->assertGuest();
    $this->assertDatabaseHas('accounts', [
        'username' => 'tsar',
        'full_name' => 'Tsar User',
        'email' => 'tsar@example.com',
    ]);
    expect(Hash::check('Sp1ce-R0ute-Long!', Account::query()->firstOrFail()->password_hash))->toBeTrue();
});

test('registration rejects mismatched password confirmation', function () {
    $this->post('/register', [
        'username' => 'tsar',
        'full_name' => 'Tsar User',
        'email' => 'tsar@example.com',
        'password' => 'Sp1ce-R0ute-Long!',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
});

test('login accepts email or username and errors on bad credentials', function () {
    $account = Account::factory()->create([
        'username' => 'tsar',
        'email' => 'tsar@example.com',
    ]);

    $this->post('/login', ['login' => 'tsar@example.com', 'password' => 'password'])
        ->assertRedirect(route('home'));
    $this->assertAuthenticated();
    expect($account->refresh()->last_login)->not->toBeNull();

    Auth::logout();

    $this->post('/login', ['login' => 'tsar', 'password' => 'password'])
        ->assertRedirect(route('home'));
    $this->assertAuthenticated();

    Auth::logout();

    $this->post('/login', ['login' => 'tsar@example.com', 'password' => 'wrong'])
        ->assertSessionHasErrors('login');
    $this->assertGuest();
});

test('login can remember an account', function () {
    $account = Account::factory()->create();

    $response = $this->post('/login', [
        'login' => $account->email,
        'password' => 'password',
        'remember' => '1',
    ])->assertRedirect(route('home'));

    expect($account->refresh()->remember_token)->not->toBeNull();
    expect(collect($response->headers->getCookies())->contains(
        fn (Cookie $cookie): bool => str_starts_with($cookie->getName(), 'remember_')
    ))->toBeTrue();
});

test('logout destroys the session', function () {
    $this->actingAs(Account::factory()->create())->post('/logout')->assertRedirect(route('login'));
    $this->assertGuest();
});
