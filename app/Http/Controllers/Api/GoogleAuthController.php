<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class GoogleAuthController extends BaseController
{
    /**
     * Initiate Google OAuth authorization
     */
    public function authorize(Request $request)
    {
        $googleClientId = env('GOOGLE_CLIENT_ID');
        $googleRedirectUri = env('GOOGLE_REDIRECT_URI');
        $googleAuthUrl = env('GOOGLE_AUTH_URL');
        $appScheme = env('APP_SCHEME');
        $clientBaseUrl = env('CLIENT_BASE_URL');

        if (!$googleClientId) {
            return response()->json([
                'error' => 'Missing GOOGLE_CLIENT_ID environment variable'
            ], 500);
        }

        $internalClient = $request->query('client_id');
        $redirectUri = $request->query('redirect_uri');

        // Determine platform based on redirect URI
        $platform = null;
        
        if ($redirectUri === $appScheme) {
            $platform = 'mobile';
        } elseif ($redirectUri === $clientBaseUrl) {
            $platform = 'web';
        } else {
            return response()->json([
                'error' => 'Invalid redirect_uri'
            ], 400);
        }

        // Use state to drive redirect back to platform
        $state = $platform . '|' . $request->query('state');

        // Validate client
        $idpClientId = null;
        
        if ($internalClient === 'google') {
            $idpClientId = $googleClientId;
        } else {
            return response()->json([
                'error' => 'Invalid client'
            ], 400);
        }

        if (!$state) {
            return response()->json([
                'error' => 'Invalid state'
            ], 400);
        }

        // Build the params for Google Auth URL
        $params = [
            'client_id' => $idpClientId,
            'redirect_uri' => $googleRedirectUri,
            'response_type' => 'code',
            'scope' => $request->query('scope', 'identity'),
            'state' => $state,
            'prompt' => 'select_account',
        ];

        // Redirect to Google Auth URL with params
        return redirect()->away($googleAuthUrl . '?' . http_build_query($params));
    }

    /**
     * Handle callback from Google OAuth
     */
    public function callback(Request $request)
    {
        $baseUrl = env('CLIENT_BASE_URL');
        $appScheme = env('APP_SCHEME');
        
        $combinedPlatformAndState = $request->query('state');
        
        if (!$combinedPlatformAndState) {
            return response()->json([
                'error' => 'Invalid state'
            ], 400);
        }
        
        $parts = explode('|', $combinedPlatformAndState);
        $platform = $parts[0];
        $state = $parts[1] ?? '';

        $outgoingParams = [
            'code' => $request->query('code', ''),
            'state' => $state,
        ];

        $redirectUrl = ($platform === 'web' ? $baseUrl : $appScheme);
        return redirect()->away($redirectUrl . '?' . http_build_query($outgoingParams));
    }

/**
 * Exchange authorization code for tokens
 */
public function token(Request $request)
{
    $code = $request->input('code');
    
    if (!$code) {
        return response()->json([
            'error' => 'Missing authorization code'
        ], 400);
    }

    $googleClientId = env('GOOGLE_CLIENT_ID');
    $googleClientSecret = env('GOOGLE_CLIENT_SECRET');
    $googleRedirectUri = env('GOOGLE_REDIRECT_URI');

    $client = new \GuzzleHttp\Client();
    
    try {
        $response = $client->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'client_id' => $googleClientId,
                'client_secret' => $googleClientSecret,
                'redirect_uri' => $googleRedirectUri,
                'grant_type' => 'authorization_code',
                'code' => $code,
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ]);
        
        $data = json_decode($response->getBody(), true);
        
        if (!isset($data['access_token'])) {
            return response()->json([
                'error' => 'Missing required parameters'
            ], 400);
        }

        $client = new \GuzzleHttp\Client();

        $response = $client->get('https://oauth2.googleapis.com/tokeninfo', [
            'query' => [
                'access_token' => $data['access_token'],
            ]
        ]);

        $payload = json_decode($response->getBody(), true);

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
        
        // Generate a deterministic password based on the Google ID and a server-side secret
        // This ensures the same password is generated every time for the same Google user
        $deterministicPassword = hash('sha256', $googleId . env('APP_KEY'));
        
        if (!$user) {
            // Create new user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($deterministicPassword),
                'email_verified_at' => now(),
                'google_id' => $googleId,
            ]);
        } else {
            // Update existing user if needed
            if (!$user->google_id) {
                $user->update([
                    'google_id' => $googleId,
                    // Update password only if the user didn't have a Google ID before
                    'password' => Hash::make($deterministicPassword),
                ]);
            } else if ($user->google_id !== $googleId) {
                // Google ID changed - update it and the password
                $user->update([
                    'google_id' => $googleId,
                    'password' => Hash::make($deterministicPassword),
                ]);
            }
            // If user already has this Google ID, no need to update the password
        }

        $response = Http::asForm()->post(url('/oauth/token'), [
            'grant_type' => 'social',
            'client_id' => env('PASSPORT_SOCIAL_CLIENT_ID'),
            'client_secret' => env('PASSPORT_SOCIAL_CLIENT_SECRET'),
            'provider' => 'google', // name of provider (e.g., 'facebook', 'google' etc.)
            'access_token' => $data['access_token'],
            'scope' => '',
        ]);

        $authResponse = $response->json();

        //return response()->json([
        //    'data' => $authResponse,
        //    'message' => 'Je suis ici'
        //], 200);

        //dd($tokens);

        //'username' => $email,
        //'password' => $deterministicPassword, // Use the deterministic password

        //$authResponse = $response->json();

        // Optional: log for debugging
        // Log::info('OAuth response', $authResponse);

        if (
            isset($authResponse['token_type'], $authResponse['access_token'], $authResponse['refresh_token'], $authResponse['expires_in'])
        ) {
            return $this->sendResponse([
                'email' => $email,
                'token_type' => $authResponse['token_type'],
                'expires_in' => $authResponse['expires_in'],
                'token' => $authResponse['access_token'],
                'refresh_token' => $authResponse['refresh_token'],
                'client_type' => 'social',
            ], 'Login successful.');
        } elseif (isset($authResponse['error'])) {
            return $this->sendError($authResponse['error_description'] ?? 'Login failed', [], 400);
        } else {
            return $this->sendError('Invalid OAuth response.', [], 500);
        }
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Decode a JWT token to get the payload
     */
    private function decodeJwt($token)
    {
        list($header, $payload, $signature) = explode('.', $token);
        
        // Base64 decode and convert to JSON
        $payloadJson = base64_decode(str_replace(['-', '_'], ['+', '/'], $payload));
        
        return json_decode($payloadJson, true);
    }
}
