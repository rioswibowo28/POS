<?php

if (!function_exists('license_config')) {
    /**
     * Get license configuration value with fallback to database settings
     * 
     * This helper provides safe access to license config that first checks
     * the database settings table, then falls back to config/license.php
     * 
     * @param string $key The config key (e.g., 'server_url', 'license_key')
     * @param mixed $default Default value if not found
     * @return mixed
     */
    function license_config(string $key, $default = null)
    {
        // Map of database setting keys to config keys
        $dbKeyMap = [
            'server_url' => 'license_server_url',
            'license_key' => 'license_key',
            'check_interval' => 'license_check_interval',
            'grace_period' => 'license_grace_period',
            'auto_check' => 'license_auto_check',
        ];
        
        // Try to get from database first
        try {
            if (isset($dbKeyMap[$key]) && class_exists(\App\Models\Setting::class)) {
                $dbKey = $dbKeyMap[$key];
                $value = \App\Models\Setting::get($dbKey, null);
                
                if ($value !== null) {
                    // Type casting for specific keys
                    if (in_array($key, ['check_interval', 'grace_period'])) {
                        return (int) $value;
                    } elseif ($key === 'auto_check') {
                        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    }
                    return $value;
                }
            }
        } catch (\Exception $e) {
            // Database not available or error occurred, fall back to config
            \Log::debug('license_config: Database fallback failed, using config', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
        }
        
        // Fallback to config file
        return config("license.{$key}", $default);
    }
}

if (!function_exists('setting')) {
    /**
     * Get or set application settings
     * 
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function setting(?string $key = null, $default = null)
    {
        if ($key === null) {
            return app(\App\Repositories\SettingRepository::class)->getAllSettings();
        }
        
        try {
            if (class_exists(\App\Models\Setting::class)) {
                return \App\Models\Setting::get($key, $default);
            }
        } catch (\Exception $e) {
            // Database not available
        }
        
        return $default;
    }
}
