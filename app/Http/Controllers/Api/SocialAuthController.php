<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;

class SocialAuthController extends BaseController
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function redirectToGoogle(Request $request)
    {
        $redirectUrl = Socialite::driver('google')->redirect();

        return response()->json(['redirect_url' => $redirectUrl]);
    }

    /**
     * Handle Google callback and authenticate the user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            // Get user information from Google
            $googleUser = Socialite::driver('google')->user();

            // Find or create user
            $user = User::where('email', $googleUser->email)->first();

            // If user doesn't exist, create a new one
            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => Hash::make(Str::random(16)),
                    'email_verified_at' => now(), // Google accounts are already verified
                    'google_id' => $googleUser->id,
                    //'avatar' => $googleUser->avatar,
                ]);
            } else {
                // Update Google ID and avatar if they've changed
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                ]);
            }

            // Create access token with refresh token
            $tokenResult = $user->createToken('Google OAuth');

            // Return token response
            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $tokenResult->accessToken,
                    'refresh_token' => $tokenResult->refreshToken,
                    'token_type' => 'Bearer',
                    'expires_in' => config('passport.tokens_expire_in') * 60,
                    'user' => $user->only(['id', 'name', 'email', 'avatar'])
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong with the Google authentication',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Google auth with an ID token from mobile app.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleGoogleToken(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        try {
            // Exchange the authorization code for tokens
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code'          => $request->code,
                'client_id'     => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri'  => 'http://localhost:8081',
                'grant_type'    => 'authorization_code',
            ]);

            if ($response->failed()) {
                logger()->error('Google token exchange failed', ['body' => $response->body()]);
                return response()->json(['success' => false, 'message' => 'Google token exchange failed.']);
            }

            $tokens = $response->json();

            if (isset($tokens['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google token exchange failed',
                    'error'   => $tokens['error_description'] ?? $tokens['error'],
                ], 400);
            }

            // Verify the Google ID token
            if (isset($tokens['id_token'])) {
                $client = new \Google_Client(['client_id' => config('services.google.client_id')]);
                $payload = $client->verifyIdToken($tokens['id_token']);

                if (!$payload) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid Google token'
                    ], 401);
                }

                // Extract user info from payload
                $googleId = $payload['sub'];
                $email = $payload['email'];
                $name = $payload['name'] ?? explode('@', $email)[0];

                // Find or create user
                $user = User::where('email', $email)->first();

                if (!$user) {
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make(Str::random(16)),
                        'email_verified_at' => now(),
                        'google_id' => $googleId,
                    ]);
                } else {
                    $user->update([
                        'google_id' => $googleId,
                    ]);
                }

                $response = Http::asForm()->post(url('/oauth/token'), [
                    'grant_type' => 'password',
                    'client_id' => env('CLIENT_ID'),
                    'client_secret' => env('CLIENT_SECRET'),
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
                    ], 'Login successful.');
                } elseif (isset($authResponse['error'])) {
                    return $this->sendError($authResponse['error_description'] ?? 'Login failed', [], 400);
                } else {
                    return $this->sendError('Invalid OAuth response.', [], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong with the Google authentication',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
