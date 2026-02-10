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
use App\Models\Company;
use App\Models\Driver;
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
        $roleInput = strtolower((string) $request->input('role'));
        $roleMap = [
            'client' => ['name' => 'customer', 'id' => 1],
            'customer' => ['name' => 'customer', 'id' => 1],
            'societe' => ['name' => 'company', 'id' => 2],
            'société' => ['name' => 'company', 'id' => 2],
            'company' => ['name' => 'company', 'id' => 2],
            'chauffeur' => ['name' => 'driver', 'id' => 3],
            'driver' => ['name' => 'driver', 'id' => 3],
        ];

        $roleData = $roleMap[$roleInput] ?? ['name' => 'customer', 'id' => 1];
        $roleName = $roleData['name'];
        $roleId = $roleData['id'];

        $user = null;

        DB::transaction(function () use ($request, $roleName, $roleId, &$user) {
            // On force l'id du rôle selon la convention métier
            $role = Role::firstOrCreate(['id' => $roleId, 'name' => $roleName]);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $roleId,
            ]);

            $user->roles()->attach($roleId);

            // Enregistrement conditionnel selon le rôle
            if ($roleName === 'company') {
                $companyFiles = [];
                if ($request->hasFile('kbis')) {
                    $companyFiles['kbis'] = $request->file('kbis')->store('documents/kbis', 'public');
                }
                if ($request->hasFile('rib')) {
                    $companyFiles['rib'] = $request->file('rib')->store('documents/rib', 'public');
                }
                if ($request->hasFile('assurance_rc_pro')) {
                    $companyFiles['assurance_rc_pro'] = $request->file('assurance_rc_pro')->store('documents/assurance_rc_pro', 'public');
                }
                Company::create(array_merge([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                    'company_name' => $request->input('company_name') ?? $request->name,
                    'company_address' => $request->input('company_address'),
                    'manager_name' => $request->input('manager_name'),
                    'company_zip_code' => $request->input('company_zip_code'),
                    'company_city' => $request->input('company_city'),
                    'company_country' => $request->input('company_country'),
                    'driver_count' => $request->input('driver_count'),
                    'company_iban' => $request->input('company_iban'),
                    'bic_code' => $request->input('bic_code'),
                ], $companyFiles));
            } else if ($roleName === 'driver') {
                $driverFiles = [];
                if ($request->hasFile('drivingLicense')) {
                    $driverFiles['driving_license'] = $request->file('drivingLicense')->store('documents/driving_license', 'public');
                }
                if ($request->hasFile('idCard')) {
                    $driverFiles['id_card'] = $request->file('idCard')->store('documents/id_card', 'public');
                }
                if ($request->hasFile('insurance')) {
                    $driverFiles['insurance'] = $request->file('insurance')->store('documents/insurance', 'public');
                }
                if ($request->hasFile('vtcCard')) {
                    $driverFiles['vtc_card'] = $request->file('vtcCard')->store('documents/vtc_card', 'public');
                }
                Driver::create(array_merge([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                    'is_available' => $request->input('is_available', true),
                    'license_type' => $request->input('license_type'),
                    'experience' => $request->input('experience'),
                    'insurance_issue_date' => $request->input('insurance_issue_date'),
                    'insurance_expiry_date' => $request->input('insurance_expiry_date'),
                    'id_issue_date' => $request->input('id_issue_date'),
                    'id_expiry_date' => $request->input('id_expiry_date'),
                    'license_issue_date' => $request->input('license_issue_date'),
                    'license_expiry_date' => $request->input('license_expiry_date'),
                    'pro_card_issue_date' => $request->input('pro_card_issue_date'),
                    'pro_card_expiry_date' => $request->input('pro_card_expiry_date'),
                ], $driverFiles));
            }

            $code = mt_rand(100000, 999999);

            EmailVerificationCode::create([
                'user_id' => $user->id,
                'code' => $code,
                'expires_at' => now()->addHours(2),
            ]);

            Mail::to($user->email)->send(new VerificationCodeMail($code));
        });

        if (!$user) {
            return $this->sendError('Failed to create user.', [], 500);
        }

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

        if (!$user) {
            RateLimiter::hit($key, 60);
            info('Login error: Utilisateur introuvable avec cet email.', ['email' => $request->email]);
            return $this->sendError('Utilisateur introuvable avec cet email.', ['email' => $request->email], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60);
            info('Login error: Mot de passe incorrect.', ['email' => $request->email]);
            return $this->sendError('Mot de passe incorrect.', ['email' => $request->email], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            info('Login error: Email non vérifié.', ['email_verified_at' => $user->email_verified_at, 'email' => $user->email]);
            return $this->sendError('Email non vérifié.', ['email_verified_at' => $user->email_verified_at], 403);
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