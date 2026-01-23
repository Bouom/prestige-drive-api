<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\VerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [PasswordResetController::class, 'requestReset']);
Route::post('/verify-reset-code', [PasswordResetController::class, 'verifyCode']);
Route::post('/resend-reset-code', [PasswordResetController::class, 'resendCode']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

// Google OAuth endpoints
Route::get('/auth/authorize', [GoogleAuthController::class, 'authorize']);
Route::get('/auth/callback', [GoogleAuthController::class, 'callback']);
Route::post('/auth/token', [GoogleAuthController::class, 'token']);

Route::get('/google-redirect', [SocialAuthController::class, 'redirectToGoogle']);
Route::get('/google-callback', [SocialAuthController::class, 'handleGoogleCallback']);
Route::post('/google-token', [SocialAuthController::class, 'handleGoogleToken']);

Route::post('/verify-email', [VerificationController::class, 'verify']);
Route::post('/check-verification', [VerificationController::class, 'checkVerificationStatus']);
Route::post('/resend-verification-code', [VerificationController::class, 'resend']);

Route::middleware(['auth:api', 'verified'])->group(function () {
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });
});
