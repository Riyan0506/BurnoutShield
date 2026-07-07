<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GoogleCalendarController;
use Illuminate\Support\Facades\Route;

// ─── Public ─────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ─── Guest only ─────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class, 'showForm'])->name('login');
    Route::post('/login',   [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register',[RegisterController::class, 'register']);
});

// ─── Authenticated employee routes ──────────────────────────
Route::middleware(['web', 'auth'])->prefix('user')->name('')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile',       [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',       [ProfileController::class, 'update'])->name('profile.update');

    // Assessments
    Route::get('/assessments/create',  [AssessmentController::class, 'create'])->name('assessments.create');
    Route::post('/assessments',        [AssessmentController::class, 'store'])->name('assessments.store');
    Route::get('/assessments/history', [AssessmentController::class, 'history'])->name('assessments.history');

    // Prediction results
    Route::get('/predictions/{prediction}', [PredictionController::class, 'show'])->name('predictions.show');

    // Google Calendar
    Route::get('/calendar',           [GoogleCalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/connect',   [GoogleCalendarController::class, 'connect'])->name('calendar.connect');
    Route::post('/calendar/sync',     [GoogleCalendarController::class, 'sync'])->name('calendar.sync');
    Route::delete('/calendar/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('calendar.disconnect');
});

// Google Calendar OAuth callback - NO auth middleware, we handle authentication manually
Route::middleware(['web'])->group(function () {
    Route::get('/user/calendar/callback', [GoogleCalendarController::class, 'callback'])->name('calendar.callback');
});
