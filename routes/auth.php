<?php

use App\Http\Controllers\Auth\LoginRegisterController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes (guest only)
Route::middleware('guest')->group(function () {
    // Register
    Route::get('register', [LoginRegisterController::class, 'register_form'])->name('register');
    Route::post('register', [LoginRegisterController::class, 'register'])->name('register.store');
    
    // Login
    Route::get('login', [LoginRegisterController::class, 'login_form'])->name('login');
    Route::post('login', [LoginRegisterController::class, 'login'])->name('login.store');
});

// Protected routes (auth only)
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('logout', [LoginRegisterController::class, 'logout'])->name('logout');

    // Email verification routes
    Route::get('verify-email', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->intended(route('dashboard'));
    })->middleware(['signed'])->name('verification.verify');

    Route::post('email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware(['throttle:6,1'])->name('verification.send');
});
