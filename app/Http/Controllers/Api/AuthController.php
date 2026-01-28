<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\DeleteAccountRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\AuthTokenResource;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Models\Role;
use App\Models\EmailVerificationCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Laravel\Passport\Token;
use Laravel\Passport\RefreshToken;

/**
 * @tags Authentication
 */
class AuthController extends BaseController
{
    /**
     * Register a new user.
     *
     * Creates a new user account and sends a verification email with a 6-digit code.
     * The user must verify their email before they can log in.
     *
     * @unauthenticated
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $code = mt_rand(100000, 999999);

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addHours(2),
        ]);

        Mail::to($user->email)->send(new VerificationCodeMail($code));

        $role = Role::firstOrCreate(['name' => 'customer']);
        $user->roles()->attach($role);

        return $this->sendResponse(
            new UserResource($user->load('roles')),
            'User registered successfully. Please verify your email.'
        );
    }

    /**
     * Login user.
     *
     * Authenticates the user and returns access tokens.
     * The user must have verified their email before logging in.
     * Rate limited to 5 attempts per minute.
     *
     * @unauthenticated
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Rate limiting - 5 attempts per minute per email + IP
        $key = 'login:' . $request->ip() . ':' . $request->email;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return $this->sendError(
                'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
                ['retry_after' => $seconds],
                429
            );
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60);

            return $this->sendError('Invalid credentials.', [], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return $this->sendError('Email not verified.', [], 403);
        }

        // Clear rate limiter on successful login
        RateLimiter::clear($key);

        // Create Personal Access Token using Passport
        $tokenResult = $user->createToken('Personal Access Token');
        
        return $this->sendResponse(
            new AuthTokenResource([
                'email' => $user->email,
                'token_type' => 'Bearer',
                'expires_in' => config('passport.tokens_expire_in', 525600) * 60, // in seconds
                'token' => $tokenResult->accessToken,
                'refresh_token' => null, // Personal Access Tokens don't have refresh tokens
                'client_type' => 'personal',
            ]),
            'Login successful.'
        );
    }

    /**
     * Refresh access token.
     *
     * For Personal Access Tokens, this creates a new token.
     * The user must be authenticated to refresh their token.
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->sendError('Unauthenticated.', [], 401);
        }

        // Revoke current token
        $request->user()->token()->revoke();

        // Create new token
        $tokenResult = $user->createToken('Personal Access Token');

        return $this->sendResponse(
            new AuthTokenResource([
                'email' => $user->email,
                'token_type' => 'Bearer',
                'expires_in' => config('passport.tokens_expire_in', 525600) * 60,
                'token' => $tokenResult->accessToken,
                'refresh_token' => null,
                'client_type' => 'personal',
            ]),
            'Token refreshed successfully.'
        );
    }

    /**
     * Get authenticated user.
     *
     * Returns the currently authenticated user's profile.
     * 
     * @authenticated
     */
    #[Authenticated]
    public function me(Request $request): JsonResponse
    {
        return $this->sendResponse(
            new UserResource($request->user()->load('roles')),
            'User retrieved successfully.'
        );
    }

    /**
     * Update user profile.
     *
     * Updates the authenticated user's profile information.
     * If email is changed, the user must verify the new email.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $emailChanged = false;

        if ($request->has('email') && $request->email !== $user->email) {
            $emailChanged = true;
        }

        $user->update($request->validated());

        if ($emailChanged) {
            $user->email_verified_at = null;
            $user->save();

            EmailVerificationCode::where('user_id', $user->id)->delete();

            $code = mt_rand(100000, 999999);

            EmailVerificationCode::create([
                'user_id' => $user->id,
                'code' => $code,
                'expires_at' => now()->addHours(2),
            ]);

            Mail::to($user->email)->send(new VerificationCodeMail($code));

            return $this->sendResponse(
                new UserResource($user->fresh()->load('roles')),
                'Profile updated. Please verify your new email address.'
            );
        }

        return $this->sendResponse(
            new UserResource($user->fresh()->load('roles')),
            'Profile updated successfully.'
        );
    }

    /**
     * Change user password.
     *
     * Changes the authenticated user's password.
     * Requires the current password for verification.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Current password is incorrect.', [], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->sendResponse([], 'Password changed successfully.');
    }

    /**
     * Delete user account.
     *
     * Permanently deletes the authenticated user's account.
     * Requires password confirmation and typing "DELETE".
     */
    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return $this->sendError('Password is incorrect.', [], 422);
        }

        DB::transaction(function () use ($user) {
            EmailVerificationCode::where('user_id', $user->id)->delete();
            $user->tokens()->delete();
            $user->roles()->detach();
            $user->delete();
        });

        return $this->sendResponse([], 'Account deleted successfully.');
    }

    /**
     * Logout user.
     *
     * Revokes the current access token for the authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return $this->sendResponse([], 'Logout successful.');
    }

    /**
     * Logout from all devices.
     *
     * Revokes all access tokens for the authenticated user.
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->each(function ($token) {
            $token->revoke();
        });

        return $this->sendResponse([], 'Logged out from all devices successfully.');
    }
}