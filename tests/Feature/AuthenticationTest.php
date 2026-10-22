<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login from protected pages', function () {
    $this->get('/')->assertRedirect(route('login'));
});

test('guests can reach the login and register pages', function () {
    $this->get('/login')->assertOk();
    $this->get('/register')->assertOk();
});

test('authenticated users are redirected away from guest pages', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/login')->assertRedirect(route('home'));
    $this->actingAs($user)->get('/register')->assertRedirect(route('home'));
});

test('registration creates the user, logs them in, and redirects home', function () {
    $this->post('/register', [
        'name' => 'tsar',
        'email' => 'tsar@example.com',
        'password' => 'Sp1ce-R0ute-Long!',
        'password_confirmation' => 'Sp1ce-R0ute-Long!',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'tsar@example.com']);
    $this->get('/')->assertOk();
});

test('registration rejects mismatched password confirmation', function () {
    $this->post('/register', [
        'name' => 'tsar',
        'email' => 'tsar@example.com',
        'password' => 'Sp1ce-R0ute-Long!',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
});

test('login accepts email or username and errors on bad credentials', function () {
    $user = User::factory()->create([
        'name' => 'tsar',
        'email' => 'tsar@example.com',
    ]);

    $this->post('/login', ['login' => 'tsar@example.com', 'password' => 'password'])
        ->assertRedirect(route('home'));
    $this->assertAuthenticated();

    Auth::logout();

    $this->post('/login', ['login' => 'tsar', 'password' => 'password'])
        ->assertRedirect(route('home'));
    $this->assertAuthenticated();

    Auth::logout();

    $this->post('/login', ['login' => 'tsar@example.com', 'password' => 'wrong'])
        ->assertSessionHasErrors('login');
    $this->assertGuest();
});

test('logout destroys the session', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));
    $this->assertGuest();
});
