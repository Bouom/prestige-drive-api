<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Passport OAuth Clients
    |--------------------------------------------------------------------------
    */
    'passport' => [
        'password_client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
        'password_client_secret' => env('PASSPORT_PASSWORD_CLIENT_SECRET'),
        'social_client_id' => env('PASSPORT_SOCIAL_CLIENT_ID'),
        'social_client_secret' => env('PASSPORT_SOCIAL_CLIENT_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google OAuth
    |--------------------------------------------------------------------------
    */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
        'auth_url' => env('GOOGLE_AUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth'),
        'app_scheme' => env('APP_SCHEME'),
        'client_base_url' => env('CLIENT_BASE_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stripe Configuration
    |--------------------------------------------------------------------------
    */

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
        'currency' => env('LCP_CURRENCY', 'eur'),
        'sandbox' => env('STRIPE_SANDBOX', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Maps Configuration
    |--------------------------------------------------------------------------
    */

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
        'enabled' => env('GOOGLE_MAPS_API_KEY') !== null,
    ],

];
