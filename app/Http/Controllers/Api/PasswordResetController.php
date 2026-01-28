<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\PasswordResetRequestRequest;
use App\Http\Requests\Auth\VerifyResetCodeRequest;
use App\Http\Requests\Auth\ResetPasswordWithTokenRequest;
use App\Http\Requests\Auth\ResendVerificationRequest;
use App\Http\Resources\VerificationTokenResource;
use App\Mail\PasswordResetCodeMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * @tags Password Reset
 */
class PasswordResetController extends BaseController
{
    /**
     * Request a password reset code.
     *
     * Sends a 6-digit verification code to the user's email address.
     * Rate limited to 3 requests per hour per IP address.
     *
     * @unauthenticated
     */
    public function requestReset(PasswordResetRequestRequest $request): JsonResponse
    {
        $email = $request->email;

        // Rate limiting - 3 requests per hour
        $key = 'password-reset:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            return $this->sendError(
                'Too many reset attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.',
                ['retry_after' => $seconds],
                429
            );
        }

        // Increment counter with 60-minute decay
        RateLimiter::hit($key, 60 * 60);

        // Delete any existing tokens for this email
        PasswordResetToken::where('email', $email)->delete();

        // Generate a secure 6-digit code
        $code = (string) random_int(100000, 999999);

        // Store the token
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $code,
            'expires_at' => now()->addHours(2),
            'attempts' => 0,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        // Send the reset code email
        Mail::to($email)->send(new PasswordResetCodeMail($code));

        return $this->sendResponse([], 'Password reset code has been sent to your email.');
    }

    /**
     * Verify the reset code.
     *
     * Validates the 6-digit code and returns a verification token
     * that must be used to reset the password.
     * Rate limited to 5 attempts per 30 minutes per IP address.
     *
     * @unauthenticated
     */
    public function verifyCode(VerifyResetCodeRequest $request): JsonResponse
    {
        $email = $request->email;
        $code = $request->code;

        // Rate limiting for verification attempts
        $key = 'verify-reset:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return $this->sendError(
                'Too many verification attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.',
                ['retry_after' => $seconds],
                429
            );
        }

        // Find the token
        $resetToken = PasswordResetToken::where('email', $email)
            ->where('token', $code)
            ->first();

        if (!$resetToken) {
            RateLimiter::hit($key, 30 * 60); // 30 minutes

            return $this->sendError('Invalid reset code.', [], 400);
        }

        if (!$resetToken->isValid()) {
            RateLimiter::hit($key, 30 * 60);

            if (!$resetToken->expires_at->isFuture()) {
                return $this->sendError('Reset code has expired.', [], 400);
            }

            return $this->sendError('Too many failed attempts for this code.', [], 400);
        }

        // Code is valid, increment attempts
        $resetToken->incrementAttempts();

        // Generate a separate verification token for the reset process
        $verificationToken = bin2hex(random_bytes(32));

        // Store the temp token
        $resetToken->verification_token = $verificationToken;
        $resetToken->save();

        // Clear the rate limiting
        RateLimiter::clear($key);

        return $this->sendResponse(
            new VerificationTokenResource(['verification_token' => $verificationToken]),
            'Code verified successfully.'
        );
    }

    /**
     * Resend the reset code.
     *
     * Generates and sends a new 6-digit verification code.
     * Rate limited to 1 request per minute per IP address.
     *
     * @unauthenticated
     */
    public function resendCode(ResendVerificationRequest $request): JsonResponse
    {
        $email = $request->email;

        // Check if user exists
        $user = User::where('email', $email)->first();
        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        // Rate limiting - allow resending once per minute
        $key = 'resend-reset:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);

            return $this->sendError(
                'Please wait ' . $seconds . ' seconds before requesting another code.',
                ['retry_after' => $seconds],
                429
            );
        }

        // Increment counter with 1-minute decay
        RateLimiter::hit($key, 60);

        // Delete any existing tokens for this email
        PasswordResetToken::where('email', $email)->delete();

        // Generate a secure 6-digit code
        $code = (string) random_int(100000, 999999);

        // Store the token
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $code,
            'expires_at' => now()->addHours(2),
            'attempts' => 0,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        // Send the reset code email
        Mail::to($email)->send(new PasswordResetCodeMail($code));

        return $this->sendResponse([], 'Password reset code has been resent to your email.');
    }

    /**
     * Reset the password using verification token.
     *
     * Resets the user's password using the verification token
     * obtained from the verifyCode endpoint.
     *
     * @unauthenticated
     */
    public function resetPassword(ResetPasswordWithTokenRequest $request): JsonResponse
    {
        $email = $request->email;
        $token = $request->verification_token;

        // Find the reset record
        $resetToken = PasswordResetToken::where('email', $email)
            ->where('verification_token', $token)
            ->first();

        if (!$resetToken || !$resetToken->isValid()) {
            return $this->sendError('Invalid or expired reset token.', [], 400);
        }

        // Update the user's password
        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the reset token
        $resetToken->delete();

        return $this->sendResponse([], 'Password has been reset successfully.');
    }
}
