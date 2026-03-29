<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Resources\AuthTokenResource;
use App\Http\Resources\UserResource;
use App\Mail\VerificationCodeMail;
use App\Models\Company;
use App\Models\DocumentType;
use App\Models\DriverProfile;
use App\Models\EmailVerificationCode;
use App\Models\RideQuote;
use App\Models\User;
use App\Models\UserType;
use App\Services\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Passport\Token;

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
        $fileStorageService = app(FileStorageService::class);
        $userTypeName = $request->input('user_type', 'client');
        $userType = UserType::where('name', $userTypeName)->first();

        $user = DB::transaction(function () use ($request, $userType, $userTypeName, $fileStorageService) {
            $user = User::create([
                'user_type_id' => $userType->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'postal_code' => $request->postal_code,
                'city' => $request->city,
                'language' => $request->input('language', 'fr'),
                'timezone' => $request->input('timezone', 'Europe/Paris'),
            ]);

            if ($userTypeName === 'driver') {
                $this->createDriverProfile($request, $user, $fileStorageService);
            } elseif ($userTypeName === 'company') {
                $this->createCompanyProfile($request, $user, $fileStorageService);
            }

            return $user;
        });

        $code = mt_rand(100000, 999999);

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addHours(2),
        ]);

        Mail::to($user->email)->send(new VerificationCodeMail($code));

        $this->claimOrphanQuotes($request->input('guest_token'), $user);

        return $this->sendResponse(
            new UserResource($user->load('userType')),
            'Utilisateur enregistré avec succès. Veuillez vérifier votre adresse e-mail.'
        );
    }

    /**
     * Create driver profile and upload documents during registration.
     */
    private function createDriverProfile(RegisterRequest $request, User $user, FileStorageService $fileStorageService): void
    {
        $driver = DriverProfile::create([
            'user_id' => $user->id,
            'license_type_id' => $request->license_type_id,
            'license_number' => $request->license_number,
            'license_issued_at' => $request->license_issued_at,
            'license_expires_at' => $request->license_expires_at,
            'professional_card_number' => $request->professional_card_number,
            'years_experience' => $request->years_experience,
            'employment_type' => $request->employment_type,
            'max_passengers' => $request->max_passengers,
            'iban' => $request->iban,
            'bic' => $request->bic,
            'bank_account_holder' => $request->bank_account_holder,
            'bio' => $request->bio,
            'is_available' => true,
        ]);

        $documentMap = [
            'document_id_card' => ['type' => 'id_card', 'issued' => 'id_card_issued_at', 'expires' => 'id_card_expires_at'],
            'document_driving_license' => ['type' => 'driving_license', 'issued' => 'driving_license_issued_at', 'expires' => 'driving_license_expires_at'],
            'document_vtc_card' => ['type' => 'vtc_card', 'issued' => 'vtc_card_issued_at', 'expires' => 'vtc_card_expires_at'],
        ];

        foreach ($documentMap as $fileField => $meta) {
            if ($request->hasFile($fileField)) {
                $docType = DocumentType::where('name', $meta['type'])->first();
                if ($docType) {
                    $fileStorageService->uploadDocument(
                        $request->file($fileField),
                        $driver,
                        $docType->id,
                        [
                            'issued_at' => $request->input($meta['issued']),
                            'expires_at' => $request->input($meta['expires']),
                        ]
                    );
                }
            }
        }
    }

    /**
     * Create company profile and upload documents during registration.
     */
    private function createCompanyProfile(RegisterRequest $request, User $user, FileStorageService $fileStorageService): void
    {
        $company = Company::create([
            'legal_name' => $request->legal_name,
            'trade_name' => $request->trade_name,
            'registration_number' => $request->registration_number,
            'vat_number' => $request->vat_number,
            'email' => $request->company_email,
            'phone' => $request->company_phone,
            'website' => $request->website,
            'address' => $request->company_address,
            'postal_code' => $request->company_postal_code,
            'city' => $request->company_city,
            'country' => $request->company_country,
            'representative_name' => $request->representative_name,
            'iban' => $request->company_iban,
            'bic' => $request->company_bic,
            'billing_email' => $request->billing_email,
            'description' => $request->description,
            'total_drivers' => $request->input('driver_count', 1),
        ]);

        $company->users()->attach($user->id, [
            'role' => 'admin',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $documentMap = [
            'document_kbis' => ['type' => 'company_registration', 'issued' => null, 'expires' => null],
            'document_rib' => ['type' => 'company_bank_details', 'issued' => null, 'expires' => null],
            'document_insurance' => ['type' => 'company_insurance', 'issued' => 'insurance_issued_at', 'expires' => 'insurance_expires_at'],
        ];

        foreach ($documentMap as $fileField => $meta) {
            if ($request->hasFile($fileField)) {
                $docType = DocumentType::where('name', $meta['type'])->first();
                if ($docType) {
                    $fileStorageService->uploadDocument(
                        $request->file($fileField),
                        $company,
                        $docType->id,
                        [
                            'issued_at' => $meta['issued'] ? $request->input($meta['issued']) : null,
                            'expires_at' => $meta['expires'] ? $request->input($meta['expires']) : null,
                        ]
                    );
                }
            }
        }
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
        $key = 'login:'.$request->ip().':'.$request->email;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return $this->sendError(
                'Trop de tentatives de connexion. Veuillez réessayer dans '.$seconds.' secondes.',
                ['retry_after' => $seconds],
                429
            );
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60);

            return $this->sendError('Identifiants non valides.', [], 401);
        }

        if (! $user->is_active) {
            return $this->sendError('Votre compte a été désactivé.', [], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            return $this->sendError('Adresse e-mail non vérifiée.', [], 403);
        }

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Revoke old tokens if not remember me
        if (! $request->boolean('remember')) {
            // $request->user()->token()->revoke();
            $user->tokens()->delete();
        }

        // Clear rate limiter on successful login
        RateLimiter::clear($key);

        $this->claimOrphanQuotes($request->input('guest_token'), $user);

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
            'Connexion réussie.'
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

        if (! $user) {
            return $this->sendError('Non authentifié.', [], 401);
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
            'Jeton actualisé avec succès.'
        );
    }

    /**
     * Get authenticated user.
     *
     * Returns the currently authenticated user's profile.
     *
     * @authenticated
     */
    public function me(Request $request): JsonResponse
    {
        return $this->sendResponse(
            new UserResource(
                $request->user()->load('userType', 'driverProfile', 'companies')
            ),
            'Utilisateur récupéré avec succès.'
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
                new UserResource($user->fresh()->load('userType')),
                'Profil mis à jour. Veuillez vérifier votre nouvelle adresse e-mail.'
            );
        }

        return $this->sendResponse(
            new UserResource($user->fresh()->load('userType')),
            'Profil mis à jour avec succès.'
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

        if (! Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Le mot de passe actuel est incorrect.', [], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->sendResponse([], 'Mot de passe modifié avec succès.');
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

        if (! Hash::check($request->password, $user->password)) {
            return $this->sendError('Le mot de passe est incorrect.', [], 422);
        }

        DB::transaction(function () use ($user) {
            EmailVerificationCode::where('user_id', $user->id)->delete();
            $user->tokens()->delete();
            $user->delete();
        });

        return $this->sendResponse([], 'Compte supprimé avec succès.');
    }

    /**
     * Logout user.
     *
     * Revokes the current access token for the authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return $this->sendResponse([], 'Déconnexion réussie.');
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

        return $this->sendResponse([], 'Déconnexion de tous les appareils réussie.');
    }

    /**
     * Claim orphan ride quotes created by a guest.
     */
    private function claimOrphanQuotes(?string $guestToken, User $user): int
    {
        if (! $guestToken) {
            return 0;
        }

        return RideQuote::forGuest($guestToken)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);
    }
}
