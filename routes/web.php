<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AdminController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/login', [AuthenticatedSessionController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'login'])->name('login.submit');
Route::get('/register', [RegisteredUserController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthenticatedSessionController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/logout', [AuthenticatedSessionController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'role:patient'])->prefix('patient')->group(function ()
{
Route::get('/dashboard', [PatientController::class, 'dashboard'])->name('patient.dashboard');
Route::post('/appointments', [PatientController::class, 'storeAppointment'])->name('patient.appointments.store');
Route::put('/appointments/{id}', [PatientController::class,'updateAppointment'])->name('patient.appointments.update');
Route::delete('/appointments/{id}', [PatientController::class,'cancelAppointment'])->name('patient.appointments.cancel');
});

require __DIR__.'/auth.php';
