<?php

use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Route::middleware('captcha')->post('/register', [Auth\RegisteredUserController::class, 'store']);
    Route::get('/login', [Auth\AuthenticatedSessionController::class, 'create'])->name('login');
    Route::middleware('captcha')->post('/login', [Auth\AuthenticatedSessionController::class, 'store']);
    // Route::get('/forgot-password', [Auth\PasswordResetLinkController::class, 'create'])->name('password.request');
    // Route::post('/forgot-password', [Auth\PasswordResetLinkController::class, 'store'])->name('password.email');
    // Route::get('/reset-password/{token}', [Auth\NewPasswordController::class, 'create'])->name('password.reset');
    // Route::post('/reset-password', [Auth\NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/register', [Auth\RegisteredUserController::class, 'create'])->name('register');
    Route::get('/verify-email', [Auth\EmailVerificationPromptController::class, '__invoke'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [Auth\VerifyEmailController::class, '__invoke'])->name('verification.verify');
    Route::post('/email/verification-notification', [Auth\EmailVerificationNotificationController::class, 'store'])->name('verification.send');
    // Route::get('/confirm-password', [Auth\ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    // Route::post('/confirm-password', [Auth\ConfirmablePasswordController::class, 'store']);
    Route::post('/logout', [Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
