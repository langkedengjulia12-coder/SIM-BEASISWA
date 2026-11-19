<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Hanya tamu: halaman dan proses login (P-01)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
