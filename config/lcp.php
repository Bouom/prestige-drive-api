<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LCP VTC Business Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the business-specific configuration for the LCP VTC
    | platform. Adjust these values according to your business requirements.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Company Information
    |--------------------------------------------------------------------------
    */

    'company' => [
        'name' => env('LCP_COMPANY_NAME', 'LCP VTC - Louer un Chauffeur Prestige'),
        'email' => env('LCP_COMPANY_EMAIL', 'contact@lcp-vtc.fr'),
        'phone' => env('LCP_COMPANY_PHONE', '+33 1 23 45 67 89'),
        'address' => env('LCP_COMPANY_ADDRESS', 'Paris, France'),
        'siret' => env('LCP_COMPANY_SIRET'),
        'vat_number' => env('LCP_COMPANY_VAT'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ride Configuration
    |--------------------------------------------------------------------------
    */

    'ride' => [
        // Commission rate (percentage taken from driver earnings)
        'commission_rate' => (float) env('LCP_COMMISSION_RATE', 15.0),

        // Cancellation fee (in EUR)
        'cancellation_fee' => (float) env('LCP_CANCELLATION_FEE', 5.00),

        // Maximum ride distance (in kilometers)
        'max_distance_km' => (int) env('LCP_MAX_DISTANCE_KM', 500),

        // Driver search radius (in kilometers)
        'driver_search_radius_km' => (int) env('LCP_DRIVER_SEARCH_RADIUS_KM', 10),

        // Base price per kilometer
        'base_price_per_km' => (float) env('LCP_BASE_PRICE_PER_KM', 2.50),

        // Minimum fare
        'minimum_fare' => (float) env('LCP_MINIMUM_FARE', 10.00),

        // Long distance threshold (km)
        'long_distance_threshold' => (int) env('LCP_LONG_DISTANCE_THRESHOLD', 100),

        // Long distance surcharge (percentage)
        'long_distance_surcharge' => (float) env('LCP_LONG_DISTANCE_SURCHARGE', 0),

        // Average speed for duration calculation (km/h)
        'average_speed_kmh' => (int) env('LCP_AVERAGE_SPEED_KMH', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Driver Configuration
    |--------------------------------------------------------------------------
    */

    'driver' => [
        // Minimum rating required to remain active
        'min_rating' => (float) env('LCP_MIN_DRIVER_RATING', 4.0),

        // Auto-assign drivers to rides
        'auto_assign' => env('LCP_AUTO_ASSIGN_DRIVERS', true),

        // Time (in seconds) driver has to accept a ride
        'acceptance_timeout' => (int) env('LCP_DRIVER_ACCEPTANCE_TIMEOUT', 300),

        // Minimum acceptance rate (percentage)
        'min_acceptance_rate' => (float) env('LCP_MIN_ACCEPTANCE_RATE', 80.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Configuration
    |--------------------------------------------------------------------------
    */

    'payment' => [
        // Default currency
        'currency' => env('LCP_CURRENCY', 'EUR'),

        // Tax rate (VAT percentage)
        'tax_rate' => (float) env('LCP_TAX_RATE', 20.0),

        // Payment gateway (stripe, paypal, etc.)
        'gateway' => env('LCP_PAYMENT_GATEWAY', 'stripe'),

        // Allow cash payments
        'allow_cash' => env('LCP_ALLOW_CASH_PAYMENTS', false),

        // Driver payout frequency (days)
        'payout_frequency_days' => (int) env('LCP_PAYOUT_FREQUENCY_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Management
    |--------------------------------------------------------------------------
    */

    'documents' => [
        // Maximum file size (in MB)
        'max_size_mb' => (int) env('LCP_MAX_DOCUMENT_SIZE_MB', 5),

        // Allowed file types
        'allowed_types' => explode(',', env('LCP_ALLOWED_DOCUMENT_TYPES', 'pdf,jpg,jpeg,png')),

        // Days before expiry to send warning
        'expiry_warning_days' => (int) env('LCP_DOCUMENT_EXPIRY_WARNING_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'email' => env('LCP_EMAIL_NOTIFICATIONS', true),
        'sms' => env('LCP_SMS_NOTIFICATIONS', false),
        'push' => env('LCP_PUSH_NOTIFICATIONS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Location Services
    |--------------------------------------------------------------------------
    */

    'location' => [
        // Use OpenStreetMap (free) or Google Maps (paid)
        'use_openstreetmap' => env('USE_OPENSTREETMAP', true),

        // Geocoding service provider
        'geocoding_provider' => env('USE_OPENSTREETMAP', true) ? 'nominatim' : 'google',

        // Routing service provider
        'routing_provider' => env('USE_OPENSTREETMAP', true) ? 'osrm' : 'google',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'rate_limits' => [
        'public' => (int) env('RATE_LIMIT_PUBLIC', 60),
        'authenticated' => (int) env('RATE_LIMIT_AUTHENTICATED', 120),
        'admin' => (int) env('RATE_LIMIT_ADMIN', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */

    'features' => [
        'real_time_tracking' => env('FEATURE_REALTIME_TRACKING', false),
        'scheduled_rides' => env('FEATURE_SCHEDULED_RIDES', true),
        'multi_stop_rides' => env('FEATURE_MULTI_STOP', true),
        'company_accounts' => env('FEATURE_COMPANY_ACCOUNTS', true),
        'referral_system' => env('FEATURE_REFERRALS', false),
        'loyalty_program' => env('FEATURE_LOYALTY', false),
    ],

];
