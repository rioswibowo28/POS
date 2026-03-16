<?php

echo "=== Testing License Config Helper ===\n\n";

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "1. Checking if license_config() exists...\n";
if (function_exists('license_config')) {
    echo "   ✅ SUCCESS: license_config() function is loaded!\n\n";
    
    echo "2. Testing license_config('server_url')...\n";
    try {
        $serverUrl = \license_config('server_url', 'http://default');
        echo "   ✅ Result: " . $serverUrl . "\n\n";
    } catch (\Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n\n";
    }
    
    echo "3. Testing license_config('license_key')...\n";
    try {
        $licenseKey = \license_config('license_key', 'DEFAULT-KEY');
        echo "   ✅ Result: " . ($licenseKey ?: '(empty)') . "\n\n";
    } catch (\Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n\n";
    }
} else {
    echo "   ❌ FAILED: license_config() function not found!\n\n";
}

echo "4. Checking if setting() exists...\n";
if (function_exists('setting')) {
    echo "   ✅ SUCCESS: setting() function is loaded!\n\n";
} else {
    echo "   ❌ FAILED: setting() function not found!\n\n";
}

echo "=== Test Complete ===\n";
