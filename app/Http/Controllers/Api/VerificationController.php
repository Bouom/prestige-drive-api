<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\CheckVerificationStatusRequest;
use App\Http\Requests\Auth\ResendVerificationRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\VerificationStatusResource;
use App\Mail\VerificationCodeMail;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * @tags Email Verification
 */
class VerificationController extends BaseController
{
    /**
     * @var int Maximum verification attempts allowed
     */
    protected int $maxAttempts = 5;

    /**
     * @var int Decay time in minutes for rate limiting
     */
    protected int $decayMinutes = 30;

    /**
     * Verify email address.
     *
     * Verifies the user's email using the 6-digit code sent during registration.
     * Rate limited to 5 attempts per 30 minutes.
     *
     * @unauthenticated
     */
    public function verify(VerifyEmailRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->sendError('Utilisateur non trouvé.', [], 404);
        }

        // Rate limiting verification attempts
        $key = 'verification:'.$user->id;

        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return $this->sendError(
                'Trop de tentatives de vérification. Veuillez réessayer dans '.ceil($seconds / 60).' minutes.',
                ['retry_after' => $seconds],
                429
            );
        }

        $verificationCode = EmailVerificationCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->first();

        if (! $verificationCode || ! $verificationCode->isValid()) {
            // Increment failed attempts counter
            RateLimiter::hit($key, $this->decayMinutes * 60);

            $attemptsLeft = $this->maxAttempts - RateLimiter::attempts($key);

            $message = ! $verificationCode ? 'Code de vérification invalide.' : 'Le code de vérification a expiré.';

            return $this->sendError($message, ['attempts_left' => $attemptsLeft], 400);
        }

        // Reset failed attempts counter
        RateLimiter::clear($key);

        $user->email_verified_at = now();
        $user->save();

        $verificationCode->delete();

        return $this->sendResponse(
            new UserResource($user->load('userType')),
            'Adresse e-mail vérifiée avec succès.'
        );
    }

    /**
     * Resend verification code.
     *
     * Sends a new verification code to the user's email address.
     * Rate limited to 3 requests per hour.
     *
     * @unauthenticated
     */
    public function resend(ResendVerificationRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->sendError('Utilisateur non trouvé.', [], 404);
        }

        if ($user->email_verified_at) {
            return $this->sendError('Adresse e-mail déjà vérifiée.', [], 400);
        }

        // Rate limit code resend requests
        $key = 'verification-resend:'.$user->id;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            return $this->sendError(
                'Trop de tentatives de renvoi. Veuillez réessayer dans '.ceil($seconds / 60).' minutes.',
                ['retry_after' => $seconds],
                429
            );
        }

        // Increment the resend counter with a 60-minute decay
        RateLimiter::hit($key, 60 * 60);

        // Delete old codes
        EmailVerificationCode::where('user_id', $user->id)->delete();

        $code = mt_rand(100000, 999999);

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addHours(24),
        ]);

        Mail::to($user->email)->send(new VerificationCodeMail($code));

        return $this->sendResponse([], 'Code de vérification renvoyé.');
    }

    /**
     * Check verification status.
     *
     * Checks if the user's email is verified. If not verified,
     * sends a new verification code automatically.
     *
     * @unauthenticated
     */
    public function checkVerificationStatus(CheckVerificationStatusRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->sendError('Adresse e-mail non trouvée.', [], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->sendResponse(
                new VerificationStatusResource(['verified' => true, 'email' => $user->email]),
                'Adresse e-mail déjà vérifiée.'
            );
        }

        // Delete old codes
        EmailVerificationCode::where('user_id', $user->id)->delete();

        // Create new code
        $code = mt_rand(100000, 999999);

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addHours(2),
        ]);

        Mail::to($user->email)->send(new VerificationCodeMail($code));

        return $this->sendResponse(
            new VerificationStatusResource(['verified' => false, 'email' => $user->email]),
            'Code de vérification envoyé.'
        );
    }
}
