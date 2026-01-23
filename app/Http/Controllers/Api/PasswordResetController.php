<?php

namespace App\Http\Controllers\Api;

use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use App\Mail\PasswordResetCodeMail;
use Illuminate\Support\Facades\DB;

class PasswordResetController extends BaseController
{
    /**
     * Request a password reset code
     */
    public function requestReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        
        $email = $request->email;
        
        // Rate limiting - 3 requests per hour
        $key = 'password-reset:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'message' => 'Too many reset attempts. Please try again in ' . 
                    ceil($seconds / 60) . ' minutes.',
                'retry_after' => $seconds
            ], 429);
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
            'ip_address' => request()->ip(),
        ]);
        
        // Send the reset code email
        Mail::to($email)->send(new PasswordResetCodeMail($code));
        
        return response()->json([
            'message' => 'Password reset code has been sent to your email.'
        ]);
    }
    
    /**
     * Verify the reset code
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string',
        ]);
        
        $email = $request->email;
        $code = $request->code;
        
        // Rate limiting for verification attempts
        $key = 'verify-reset:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'message' => 'Too many verification attempts. Please try again in ' . 
                    ceil($seconds / 60) . ' minutes.',
                'retry_after' => $seconds
            ], 429);
        }
        
        // Find the token
        $resetToken = PasswordResetToken::where('email', $email)
            ->where('token', $code)
            ->first();
        
        if (!$resetToken) {
            RateLimiter::hit($key, 30 * 60); // 30 minutes
            
            return response()->json([
                'message' => 'Invalid reset code.'
            ], 400);
        }
        
        if (!$resetToken->isValid()) {
            RateLimiter::hit($key, 30 * 60);
            
            if (!$resetToken->expires_at->isFuture()) {
                return response()->json([
                    'message' => 'Reset code has expired.'
                ], 400);
            }
            
            return response()->json([
                'message' => 'Too many failed attempts for this code.'
            ], 400);
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
        
        return response()->json([
            'message' => 'Code verified successfully',
            'verification_token' => $verificationToken
        ]);
    }
    
    /**
     * Resend the reset code
     */
    public function resendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        
        $email = $request->email;
        
        // Rate limiting - allow resending once per minute
        $key = 'resend-reset:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'message' => 'Please wait ' . $seconds . ' seconds before requesting another code.',
                'retry_after' => $seconds
            ], 429);
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
            'ip_address' => request()->ip(),
        ]);
        
        // Send the reset code email
        Mail::to($email)->send(new PasswordResetCodeMail($code));
        
        return response()->json([
            'message' => 'Password reset code has been resent to your email.'
        ]);
    }
    
    /**
     * Reset the password using verification token
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'verification_token' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);
        
        $email = $request->email;
        $token = $request->verification_token;
        
        // Find the reset record
        $resetToken = PasswordResetToken::where('email', $email)
            ->where('verification_token', $token)
            ->first();
        
        if (!$resetToken || !$resetToken->isValid()) {
            return response()->json([
                'message' => 'Invalid or expired reset token.'
            ], 400);
        }
        
        // Update the user's password
        $user = User::where('email', $email)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        
        // Delete the reset token
        $resetToken->delete();
        
        return response()->json([
            'message' => 'Password has been reset successfully.'
        ]);
    }
}
