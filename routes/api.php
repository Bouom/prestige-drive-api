<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\VerificationController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::prefix('auth')->group(function () {
    // Registration & Login
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

    // Protected Routes (Authentication Required)
    Route::middleware(['auth:api', 'verified'])->group(function () {
        Route::post('/refresh-token', [AuthController::class, 'refreshToken'])->name('auth.refresh-token');
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
        Route::put('/profile', [AuthController::class, 'updateProfile'])->name('auth.update-profile');
        Route::put('/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');
        Route::delete('/account', [AuthController::class, 'deleteAccount']) ->name('auth.delete-account');
        
        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');
    });
});

// Email Verification Routes
Route::prefix('verification')->group(function () {
    Route::post('/verify', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/resend', [VerificationController::class, 'resend'])->name('verification.resend');
    Route::post('/status', [VerificationController::class, 'checkVerificationStatus'])->name('verification.status');
});

// Password Reset Routes
Route::prefix('password')->group(function () {
    Route::post('/request-reset', [PasswordResetController::class, 'requestReset'])->name('password.request-reset');
    Route::post('/verify-code', [PasswordResetController::class, 'verifyCode'])->name('password.verify-code');
    Route::post('/resend-code', [PasswordResetController::class, 'resendCode'])->name('password.resend-code');
    Route::post('/reset', [PasswordResetController::class, 'resetPassword'])->name('password.reset');
});

// Google OAuth Routes
Route::prefix('google')->group(function () {
    Route::get('/authorize', [GoogleAuthController::class, 'authorize'])->name('google.authorize');
    Route::get('/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
    Route::post('/token', [GoogleAuthController::class, 'token'])->name('google.token');
});