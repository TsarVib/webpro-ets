<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecapController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', DashboardController::class)->name('home')->middleware('auth');
Route::get('/income', [TransactionController::class, 'income'])->name('income')->middleware('auth');
Route::get('/expense', [TransactionController::class, 'expense'])->name('expense')->middleware('auth');
Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store')->middleware('auth');
Route::get('/income/export', [TransactionController::class, 'exportIncome'])->name('income.export')->middleware('auth');
Route::get('/expense/export', [TransactionController::class, 'exportExpense'])->name('expense.export')->middleware('auth');
Route::patch('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update')->middleware('auth');
Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy')->middleware('auth');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories')->middleware('auth');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store')->middleware('auth');
Route::patch('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update')->middleware('auth');
Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy')->middleware('auth');
Route::get('/recap', RecapController::class)->name('recap')->middleware('auth');
Route::get('/recap/csv', [RecapController::class, 'csv'])->name('recap.csv')->middleware('auth');
Route::get('/recap/report', [RecapController::class, 'html'])->name('recap.report')->middleware('auth');
Route::get('/recap/pdf', [RecapController::class, 'pdf'])->name('recap.pdf')->middleware('auth');
Route::get('/settings', fn () => Inertia::render('Settings'))->name('settings')->middleware('auth');
Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update')->middleware('auth');
Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password')->middleware('auth');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', fn () => Inertia::render('Login'))->name('login');
    Route::get('/register', fn () => Inertia::render('Register'))->name('register');
    Route::post('/login', [AuthController::class, 'store'])->name('login.authenticate')->middleware('throttle:5,1');
    Route::post('/register', [AuthController::class, 'create'])->name('register.create');
});

Route::post('/logout', [AuthController::class, 'destroy'])->name('logout')->middleware('auth');
