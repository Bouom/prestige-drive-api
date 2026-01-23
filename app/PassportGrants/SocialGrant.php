<?php

namespace App\PassportGrants;


use DateInterval;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Bridge\User;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\RequestEvent;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Illuminate\Support\Str;

class SocialGrant extends AbstractGrant
{
    /**
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository
     */
    public function __construct(
        RefreshTokenRepositoryInterface $refreshTokenRepository
    ) {
        $this->setRefreshTokenRepository($refreshTokenRepository);

        // Set the refresh token TTL
        $this->refreshTokenTTL = new DateInterval('P1M');
    }

    /**
     * {@inheritdoc}
     */
    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ): ResponseTypeInterface {
        // Validate request
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));
        $user = $this->validateUser($request, $client);

        // Finalize the requested scopes
        $finalizedScopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client, $user->getIdentifier());

        // Issue and persist new tokens
        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $user->getIdentifier(), $finalizedScopes);
        $this->getEmitter()->emit(new RequestEvent(RequestEvent::ACCESS_TOKEN_ISSUED, $request));
        $responseType->setAccessToken($accessToken);

        // Issue and persist refresh token if available
        $refreshToken = $this->issueRefreshToken($accessToken);

        if ($refreshToken !== null) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::REFRESH_TOKEN_ISSUED, $request));
            $responseType->setRefreshToken($refreshToken);
        }

        return $responseType;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClientEntityInterface $client
     *
     * @return UserEntityInterface
     * @throws OAuthServerException
     */
    protected function validateUser(ServerRequestInterface $request, ClientEntityInterface $client)
    {
        // Get the provider type (google, facebook, etc.)
        $provider = $this->getRequestParameter('provider', $request);
        if (is_null($provider)) {
            throw OAuthServerException::invalidRequest('provider');
        }

        // Get the access token
        $accessToken = $this->getRequestParameter('access_token', $request);
        if (is_null($accessToken)) {
            throw OAuthServerException::invalidRequest('access_token');
        }

        // Additional parameters that might be needed
        $email = $this->getRequestParameter('email', $request);
        $providerId = $this->getRequestParameter('provider_id', $request);

        // Get the user based on provider type
        $user = $this->getUserEntityByProvider(
            $provider,
            $accessToken,
            $email,
            $providerId,
            $this->getIdentifier(),
            $client
        );

        if ($user instanceof UserEntityInterface === false) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::USER_AUTHENTICATION_FAILED, $request));
            throw OAuthServerException::invalidCredentials();
        }

        return $user;
    }

    /**
     * Retrieve a user by the given provider.
     *
     * @param string $provider
     * @param string $accessToken
     * @param string|null $email
     * @param string|null $providerId
     * @param string $grantType
     * @param ClientEntityInterface $clientEntity
     *
     * @return UserEntityInterface|null
     * @throws OAuthServerException
     */
    private function getUserEntityByProvider($provider, $accessToken, $email = null, $providerId = null, $grantType, ClientEntityInterface $clientEntity)
    {
        $providerModel = config('auth.guards.api.provider');

        if (is_null($model = config('auth.providers.' . $providerModel . '.model'))) {
            throw new RuntimeException('Unable to determine authentication model from configuration.');
        }

        // Handle different providers
        if ($provider === 'google') {
            return $this->handleGoogleProvider($model, $accessToken, $email);
        } elseif ($provider === 'facebook') {
            return $this->handleFacebookProvider($model, $accessToken, $email, $providerId);
        }

        // Unknown provider
        throw OAuthServerException::invalidRequest('provider', 'Invalid provider specified');
    }

    /**
     * Handle Google provider authentication
     *
     * @param string $model
     * @param string $accessToken
     * @param string|null $email
     * @return UserEntityInterface|null
     */
    private function handleGoogleProvider($model, $accessToken, $email = null)
    {
        try {
            $client = new \GuzzleHttp\Client();

            // Verify the token
            $tokenInfoResponse = $client->get('https://www.googleapis.com/oauth2/v3/tokeninfo', [
                'query' => ['access_token' => $accessToken]
            ]);

            $tokenInfo = json_decode($tokenInfoResponse->getBody(), true);

            if (!isset($tokenInfo['sub'])) {
                return null;
            }

            $googleId = $tokenInfo['sub'];

            // Get user profile
            $userInfoResponse = $client->get('https://www.googleapis.com/oauth2/v3/userinfo', [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken]
            ]);

            $userInfo = json_decode($userInfoResponse->getBody(), true);

            // If email wasn't provided, use the one from Google
            $userEmail = $email ?: $userInfo['email'];
            $name = $userInfo['name'] ?? '';

            // Find the user
            $user = (new $model)->where('google_id', $googleId)->first();

            // If user not found by Google ID, try email
            if (!$user && $userEmail) {
                $user = (new $model)->where('email', $userEmail)->first();
            }

            // Create or update user
            if (!$user) {
                // Create new user
                $user = (new $model)->create([
                    'name' => $name,
                    'email' => $userEmail,
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(16)),
                    'google_id' => $googleId,
                ]);
            } else if ($user->google_id !== $googleId) {
                // Update user with Google ID
                $user->google_id = $googleId;
                $user->save();
            }

            return new User($user->getAuthIdentifier());
        } catch (\Exception $e) {
            Log::error('Google authentication error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Handle Facebook provider authentication
     *
     * @param string $model
     * @param string $accessToken
     * @param string|null $email
     * @param string|null $facebookId
     * @return UserEntityInterface|null
     */
    private function handleFacebookProvider($model, $accessToken, $email = null, $facebookId = null)
    {
        try {
            $client = new \GuzzleHttp\Client();

            // If no Facebook ID was provided, we need to verify the token
            if (!$facebookId) {
                $response = $client->get('https://graph.facebook.com/v18.0/me', [
                    'query' => [
                        'access_token' => $accessToken,
                        'fields' => 'id,name,email',
                    ]
                ]);

                $userData = json_decode($response->getBody(), true);

                $facebookId = $userData['id'] ?? null;
                $name = $userData['name'] ?? '';
                $userEmail = $userData['email'] ?? $email;

                if (!$facebookId) {
                    return null;
                }
            } else {
                // We have the Facebook ID but need to get user data
                $response = $client->get("https://graph.facebook.com/v18.0/{$facebookId}", [
                    'query' => [
                        'access_token' => $accessToken,
                        'fields' => 'name,email',
                    ]
                ]);

                $userData = json_decode($response->getBody(), true);
                $name = $userData['name'] ?? '';
                $userEmail = $userData['email'] ?? $email;
            }

            // Find the user
            $user = (new $model)->where('facebook_id', $facebookId)->first();

            // If user not found by Facebook ID, try email
            if (!$user && $userEmail) {
                $user = (new $model)->where('email', $userEmail)->first();
            }

            // Create or update user
            if (!$user) {
                // Create new user
                $user = (new $model)->create([
                    'name' => $name,
                    'email' => $userEmail,
                    'password' => Hash::make(Str::random(16)),
                    'facebook_id' => $facebookId,
                ]);
            } else if ($user->facebook_id !== $facebookId) {
                // Update user with Facebook ID
                $user->facebook_id = $facebookId;
                $user->save();
            }

            return new User($user->getAuthIdentifier());
        } catch (\Exception $e) {
            Log::error('Facebook authentication error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'social';
    }
}
