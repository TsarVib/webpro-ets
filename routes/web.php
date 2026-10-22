<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Home'))->name('home')->middleware('auth');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', fn () => Inertia::render('Login'))->name('login');
    Route::get('/register', fn () => Inertia::render('Register'))->name('register');
    Route::post('/login', [AuthController::class, 'store'])->name('login.authenticate');
    Route::post('/register', [AuthController::class, 'create'])->name('register.create');
});

Route::post('/logout', [AuthController::class, 'destroy'])->name('logout')->middleware('auth');
