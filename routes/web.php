<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\FinancialAdvisorController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', function () {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isAdvisor()) {
            return redirect()->route('advisor.dashboard');
        }

        return redirect()->route('client.dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware(['role:client'])->prefix('client')->group(function () {

        Route::get('/dashboard', [ClientController::class, 'dashboard'])->name('client.dashboard');

        Route::post('/expenses', [ClientController::class, 'storeExpense'])->name('client.expenses.store');
        Route::put('/expenses/{id}', [ClientController::class, 'updateExpense'])->name('client.expenses.update');
        Route::delete('/expenses/{id}', [ClientController::class, 'deleteExpense'])->name('client.expenses.delete');

        Route::post('/budgets', [ClientController::class, 'storeBudget'])->name('client.budgets.store');

        Route::post('/wallets', [ClientController::class, 'storeWallet'])->name('client.wallets.store');
        Route::delete('/wallets/{id}', [ClientController::class, 'deleteWallet'])->name('client.wallets.delete');
        Route::post('/categories', [ClientController::class, 'storeCategory'])->name('client.categories.store');

        Route::delete('/budgets/{id}', [ClientController::class, 'deleteBudget'])->name('client.budgets.delete');

        Route::post('/consultations', [ClientController::class, 'storeConsultation'])->name('client.consultations.store');
        Route::delete('/consultations/{id}', [ClientController::class, 'cancelConsultation'])->name('client.consultations.cancel');
    });

    Route::middleware(['role:advisor'])->prefix('advisor')->group(function () {

        Route::get('/dashboard', [FinancialAdvisorController::class, 'dashboard'])->name('advisor.dashboard');

        Route::post('/consultations/{id}/approve', [FinancialAdvisorController::class, 'approveConsultation'])->name('advisor.consultations.approve');
        Route::post('/consultations/{id}/decline', [FinancialAdvisorController::class, 'declineConsultation'])->name('advisor.consultations.decline');
        Route::put('/consultations/{id}/notes', [FinancialAdvisorController::class, 'updateNotes'])->name('advisor.consultations.notes');
    });

    Route::middleware(['role:admin'])->prefix('admin')->group(function () {

        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        Route::post('/advisors', [AdminController::class, 'storeAdvisor'])->name('admin.advisors.store');
        Route::delete('/advisors/{id}', [AdminController::class, 'deleteAdvisor'])->name('admin.advisors.delete');

        Route::post('/clients', [AdminController::class, 'storeClient'])->name('admin.clients.store');
        Route::delete('/clients/{id}', [AdminController::class, 'deleteClient'])->name('admin.clients.delete');
    });
});

require __DIR__ . '/auth.php';
