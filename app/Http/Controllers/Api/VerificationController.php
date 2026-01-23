<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class VerificationController extends BaseController
{
    protected $maxAttempts = 5;
    protected $decayMinutes = 30;

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Rate limiting verification attempts
        $key = 'verification:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Too many verification attempts. Please try again in ' .
                    ceil($seconds / 60) . ' minutes.',
                'retry_after' => $seconds
            ], 429);
        }

        $verificationCode = EmailVerificationCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->first();

        if (!$verificationCode || !$verificationCode->isValid()) {
            // Increment failed attempts counter
            RateLimiter::hit($key, $this->decayMinutes * 60);

            $attemptsLeft = $this->maxAttempts - RateLimiter::attempts($key);

            $message = !$verificationCode ? 'Invalid verification code' : 'Verification code has expired';
            return $this->sendError([
                'message' => $message,
                'attempts_left' => $attemptsLeft
            ], 400);
        }

        // Reset failed attempts counter
        RateLimiter::clear($key);

        $user->email_verified_at = now();
        $user->save();

        $verificationCode->delete();

        return $this->sendResponse([], 'Email verified successfully');
    }

    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->sendError([], 'User not found', 404);
        }

        if ($user->email_verified_at) {
            return $this->sendError([], 'Email already verified', 400);
        }

        // Rate limit code resend requests
        $key = 'verification-resend:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, 3)) { // Limit to 3 requests
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Too many resend attempts. Please try again in ' .
                    ceil($seconds / 60) . ' minutes.',
                'retry_after' => $seconds
            ], 429);
        }

        // Increment the resend counter with a 60-minute decay
        RateLimiter::hit($key, 60 * 60);

        EmailVerificationCode::where('user_id', $user->id)->delete();

        $code = mt_rand(100000, 999999);

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addHours(24),
        ]);

        Mail::to($user->email)->send(new VerificationCodeMail($code));

        return $this->sendResponse([], 'Verification code resent');
    }

    public function checkVerificationStatus(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->sendError('Email not found.', [], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->sendResponse(['verified' => true], 'Email already verified.');
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

        return $this->sendResponse([
            'verified' => false,
            'email' => $user->email
        ], 'Verification code sent.');
    }
}
