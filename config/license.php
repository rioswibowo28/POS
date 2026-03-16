<?php

return [

    /*
    |--------------------------------------------------------------------------
    | License Manager Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for POS Resto license verification and management.
    | Settings can be managed via Admin Panel > Settings > License API Settings
    |
    | NOTE: Config file uses env() for initial load. Database settings will
    | override these values at runtime via license_config() helper.
    |
    */

    // License Manager Server URL
    'server_url' => env('LICENSE_SERVER_URL', 'http://localhost:8000'),

    // Your License Key
    'license_key' => env('LICENSE_KEY', ''),

    // License verification settings
    'check_interval' => env('LICENSE_CHECK_INTERVAL', 86400), // Check every 24 hours (in seconds)
    
    // Grace period after expiry (in days)
    'grace_period' => env('LICENSE_GRACE_PERIOD', 7),

    // Cache settings
    'cache_key' => 'pos_resto_license_data',
    'cache_ttl' => 3600, // 1 hour

    // Offline mode settings
    'offline_mode' => env('LICENSE_OFFLINE_MODE', false),
    'offline_token' => env('LICENSE_OFFLINE_TOKEN', ''),

    // Hardware ID components (for license binding)
    'hardware_id_components' => [
        'mac' => env('LICENSE_HW_MAC', ''),
        'cpu' => env('LICENSE_HW_CPU', php_uname('m')),
        'disk' => env('LICENSE_HW_DISK', ''),
    ],

    // Auto-check on every request (set to false for better performance)
    'auto_check' => env('LICENSE_AUTO_CHECK', false),

    // Routes to exclude from license check
    'exclude_routes' => [
        'login',
        'logout',
        'register',
        'password/*',
        'license-status', // Allow checking license status
    ],

    // Enable/disable license enforcement
    'enabled' => env('LICENSE_ENFORCEMENT_ENABLED', true),

    // Notification settings
    'notify_expiry_days' => 30, // Notify when license expires in X days
    'notify_email' => env('LICENSE_NOTIFY_EMAIL', ''),

];
