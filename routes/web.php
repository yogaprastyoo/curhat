<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CurhatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.process');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');

    Route::middleware('role:student')->group(function () {
        Route::get('/curhats/create', [CurhatController::class, 'create'])->name('curhats.create');
        Route::post('/curhats', [CurhatController::class, 'store'])->name('curhats.store');
    });

    Route::get('/curhats', [CurhatController::class, 'index'])->name('curhats.index');
    Route::get('/curhats/{curhat}', [CurhatController::class, 'show'])->name('curhats.show');
    Route::post('/curhats/{curhat}/messages', [CurhatController::class, 'storeMessage'])
        ->name('curhats.messages.store');

    Route::middleware('role:counselor,psychologist')->group(function () {
        Route::post('/curhats/{curhat}/status', [CurhatController::class, 'updateStatus'])
            ->name('curhats.status');
        Route::post('/curhats/{curhat}/schedules', [CurhatController::class, 'storeSchedule'])
            ->name('curhats.schedules.store');
    });
});
