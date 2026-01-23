<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\EmailVerificationRequest;
use App\Http\Requests\LogoutRequest;
use App\Http\Requests\ResendVerificationRequest;
use App\Mail\VerificationCodeMail;
use App\Models\Role;
use App\Models\EmailVerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class AuthController extends BaseController
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request)
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

        return $this->sendResponse([], 'User registered successfully. Please verify your email.');
    }

    /**
     * Login a user.
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
    
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->sendError('Invalid credentials.');
        }
    
        if (!$user->hasVerifiedEmail()) {
            return $this->sendError('Email not verified.', [], 403);
        }
    
        $response = Http::asForm()->post(url('/oauth/token'), [
            'grant_type' => 'password',
            'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_CLIENT_SECRET'),
            'username' => $request->email,
            'password' => $request->password,
            'scope' => '',
        ]);
    
        $authResponse = $response->json();
    
        // Optional: log for debugging
        //Log::info('OAuth response', $authResponse);
    
        if (
            isset($authResponse['token_type'], $authResponse['access_token'], $authResponse['refresh_token'], $authResponse['expires_in'])
        ) {
            return $this->sendResponse([
                'email' => $request->email,
                'token_type' => $authResponse['token_type'],
                'expires_in' => $authResponse['expires_in'],
                'token' => $authResponse['access_token'],
                'refresh_token' => $authResponse['refresh_token'],
                'client_type' => 'password',
            ], 'Login successful.');
        } elseif (isset($authResponse['error'])) {
            return $this->sendError($authResponse['error_description'] ?? 'Login failed', [], 400);
        } else {
            return $this->sendError('Invalid OAuth response.', [], 500);
        }
    }
    
    public function refreshToken(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
            'client_type' => 'required|in:password,social',
        ]);
    
        $clientId = env('PASSPORT_PASSWORD_CLIENT_ID');
        $clientSecret = env('PASSPORT_PASSWORD_CLIENT_SECRET');
    
        if ($request->client_type === 'social') {
            $clientId = env('PASSPORT_SOCIAL_CLIENT_ID');
            $clientSecret = env('PASSPORT_SOCIAL_CLIENT_SECRET');
        }
    
        $response = Http::asForm()->post(url('/oauth/token'), [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => '',
        ]);
    
        if ($response->failed()) {
            return response()->json([
                'message' => 'Failed to refresh token',
                'error' => $response->json(),
            ], 401);
        }
    
        $authResponse = $response->json();
    
        return $this->sendResponse([
            'token_type' => $authResponse['token_type'],
            'expires_in' => $authResponse['expires_in'],
            'token' => $authResponse['access_token'],
            'refresh_token' => $authResponse['refresh_token'],
            'client_type' => $request->client_type,
        ], "Token successfully refreshed!");
    }

    /**
     * Logout a user.
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->sendResponse([], 'Logout successful.');
    }

}
