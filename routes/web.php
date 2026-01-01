<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\FinancialAdvisorController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication routes (Login, Register) are handled by __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Profile Management (Default Laravel Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Client Routes (Regular Users)
    |--------------------------------------------------------------------------
    | Features: Dashboard, Manage Expenses, Book Consultations
    */
    Route::middleware(['role:client'])->prefix('client')->group(function () {

        // 1. Dashboard (View Wallets, Categories, Recent Expenses)
        Route::get('/dashboard', [ClientController::class, 'dashboard'])->name('client.dashboard');

        // 2. Manage Expenses (Create, Update, Delete)
        Route::post('/expenses', [ClientController::class, 'storeExpense'])->name('client.expenses.store');
        Route::put('/expenses/{id}', [ClientController::class, 'updateExpense'])->name('client.expenses.update');
        Route::delete('/expenses/{id}', [ClientController::class, 'deleteExpense'])->name('client.expenses.delete');

        // 3. Manage Budgets
        Route::post('/budgets', [ClientController::class, 'storeBudget'])->name('client.budgets.store');

        // 4. Consultations (Book with an Advisor)
        Route::post('/consultations', [ClientController::class, 'storeConsultation'])->name('client.consultations.store');
        Route::delete('/consultations/{id}', [ClientController::class, 'cancelConsultation'])->name('client.consultations.cancel');
    });

    /*
    |--------------------------------------------------------------------------
    | Financial Advisor Routes
    |--------------------------------------------------------------------------
    | Features: View Requests, Approve/Complete Consultations
    */
    Route::middleware(['role:advisor'])->prefix('advisor')->group(function () {

        // 1. Dashboard (View assigned consultations)
        Route::get('/dashboard', [FinancialAdvisorController::class, 'dashboard'])->name('advisor.dashboard');

        // 2. Manage Consultations
        Route::post('/consultations/{id}/approve', [FinancialAdvisorController::class, 'approveConsultation'])->name('advisor.consultations.approve');
        Route::post('/consultations/{id}/decline', [FinancialAdvisorController::class, 'declineConsultation'])->name('advisor.consultations.decline');
        Route::put('/consultations/{id}/notes', [FinancialAdvisorController::class, 'updateNotes'])->name('advisor.consultations.notes');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    | Features: Manage Users and Advisors
    */
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {

        // 1. Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        // 2. Manage Financial Advisors (Create, Delete)
        Route::post('/advisors', [AdminController::class, 'storeAdvisor'])->name('admin.advisors.store');
        Route::delete('/advisors/{id}', [AdminController::class, 'deleteAdvisor'])->name('admin.advisors.delete');

        // 3. Manage Clients (Ban/Delete)
        Route::delete('/clients/{id}', [AdminController::class, 'deleteClient'])->name('admin.clients.delete');
    });
});

require __DIR__ . '/auth.php';
