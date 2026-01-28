<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\GoogleTokenRequest;
use App\Http\Resources\AuthTokenResource;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use GuzzleHttp\Client;

/**
 * @tags Google Authentication
 */
class GoogleAuthController extends BaseController
{
    /**
     * Initiate Google OAuth authorization.
     *
     * Redirects the user to Google's OAuth consent screen.
     * The redirect_uri must match either APP_SCHEME (mobile) or CLIENT_BASE_URL (web).
     *
     * @unauthenticated
     */
    public function authorize(Request $request): JsonResponse|RedirectResponse
    {
        $googleClientId = config('services.google.client_id');
        $googleRedirectUri = config('services.google.redirect_uri');
        $googleAuthUrl = config('services.google.auth_url', 'https://accounts.google.com/o/oauth2/v2/auth');
        $appScheme = config('services.google.app_scheme');
        $clientBaseUrl = config('services.google.client_base_url');

        if (!$googleClientId) {
            return $this->sendError('Missing Google client configuration.', [], 500);
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
            return $this->sendError('Invalid redirect_uri.', [], 400);
        }

        // Use state to drive redirect back to platform
        $state = $platform . '|' . $request->query('state', '');

        // Validate client
        if ($internalClient !== 'google') {
            return $this->sendError('Invalid client.', [], 400);
        }

        // Build the params for Google Auth URL
        $params = [
            'client_id' => $googleClientId,
            'redirect_uri' => $googleRedirectUri,
            'response_type' => 'code',
            'scope' => $request->query('scope', 'openid email profile'),
            'state' => $state,
            'prompt' => 'select_account',
        ];

        return redirect()->away($googleAuthUrl . '?' . http_build_query($params));
    }

    /**
     * Handle callback from Google OAuth.
     *
     * Processes the callback from Google and redirects to the appropriate platform
     * (mobile app or web client) with the authorization code.
     *
     * @unauthenticated
     */
    public function callback(Request $request): JsonResponse|RedirectResponse
    {
        $baseUrl = config('services.google.client_base_url');
        $appScheme = config('services.google.app_scheme');

        $combinedPlatformAndState = $request->query('state');

        if (!$combinedPlatformAndState) {
            return $this->sendError('Invalid state.', [], 400);
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
     * Exchange authorization code for tokens.
     *
     * Exchanges the Google authorization code for access tokens and creates
     * or updates the user account. Returns OAuth tokens for API authentication.
     *
     * @unauthenticated
     */
    public function token(GoogleTokenRequest $request): JsonResponse
    {
        $code = $request->input('code');

        $googleClientId = config('services.google.client_id');
        $googleClientSecret = config('services.google.client_secret');
        $googleRedirectUri = config('services.google.redirect_uri');

        $client = new Client();

        try {
            // Exchange code for tokens
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
                return $this->sendError('Missing access token in Google response.', [], 400);
            }

            // Get user info from Google
            $tokenInfoResponse = $client->get('https://oauth2.googleapis.com/tokeninfo', [
                'query' => [
                    'access_token' => $data['access_token'],
                ]
            ]);

            $payload = json_decode($tokenInfoResponse->getBody(), true);

            if (!$payload) {
                return $this->sendError('Invalid Google token.', [], 401);
            }

            // Extract user info from payload
            $googleId = $payload['sub'];
            $email = $payload['email'];
            $name = $payload['name'] ?? explode('@', $email)[0];

            // Find or create user
            $user = User::where('email', $email)->first();

            // Generate a deterministic password based on the Google ID and a server-side secret
            $deterministicPassword = hash('sha256', $googleId . config('app.key'));

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($deterministicPassword),
                    'email_verified_at' => now(),
                    'google_id' => $googleId,
                ]);

                // Assign default role
                $role = Role::firstOrCreate(['name' => 'customer']);
                $user->roles()->attach($role);
            } else {
                // Update existing user if needed
                if (!$user->google_id || $user->google_id !== $googleId) {
                    $user->update([
                        'google_id' => $googleId,
                        'password' => Hash::make($deterministicPassword),
                    ]);
                }
                
                // Ensure email is verified for social login
                if (!$user->hasVerifiedEmail()) {
                    $user->email_verified_at = now();
                    $user->save();
                }
            }

            // Create Personal Access Token using Passport
            $tokenResult = $user->createToken('Google Auth Token');

            return $this->sendResponse(
                new AuthTokenResource([
                    'email' => $email,
                    'token_type' => 'Bearer',
                    'expires_in' => config('passport.tokens_expire_in', 525600) * 60,
                    'token' => $tokenResult->accessToken,
                    'refresh_token' => null,
                    'client_type' => 'social',
                ]),
                'Login successful.'
            );

        } catch (\Exception $e) {
            return $this->sendError('Google authentication failed: ' . $e->getMessage(), [], 500);
        }
    }
}