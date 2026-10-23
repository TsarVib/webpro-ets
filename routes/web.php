<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Home'))->name('home')->middleware('auth');
Route::get('/income', fn () => Inertia::render('Income'))->name('income')->middleware('auth');
Route::get('/expense', fn () => Inertia::render('Expense'))->name('expense')->middleware('auth');
Route::get('/categories', fn () => Inertia::render('Categories'))->name('categories')->middleware('auth');
Route::get('/recap', fn () => Inertia::render('Recap'))->name('recap')->middleware('auth');
Route::get('/settings', fn () => Inertia::render('Settings'))->name('settings')->middleware('auth');
Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update')->middleware('auth');
Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password')->middleware('auth');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', fn () => Inertia::render('Login'))->name('login');
    Route::get('/register', fn () => Inertia::render('Register'))->name('register');
    Route::post('/login', [AuthController::class, 'store'])->name('login.authenticate');
    Route::post('/register', [AuthController::class, 'create'])->name('register.create');
});

Route::post('/logout', [AuthController::class, 'destroy'])->name('logout')->middleware('auth');
