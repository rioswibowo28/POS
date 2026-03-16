<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CLEAR LICENSE CACHE IN POS-RESTO ===" . PHP_EOL . PHP_EOL;

$cacheKey = config('license.cache_key', 'pos_resto_license_data');

if (Cache::has($cacheKey)) {
    $cachedData = Cache::get($cacheKey);
    echo "Current cached license data:" . PHP_EOL;
    echo "- License Key: " . ($cachedData['license']['license_key'] ?? 'N/A') . PHP_EOL;
    echo "- Expiry Date: " . ($cachedData['license']['expiry_date'] ?? 'N/A') . PHP_EOL;
    echo "- Days Remaining: " . ($cachedData['license']['days_remaining'] ?? 'N/A') . PHP_EOL;
    echo PHP_EOL;
    
    Cache::forget($cacheKey);
    echo "✅ Cache cleared!" . PHP_EOL;
} else {
    echo "No cached license data found." . PHP_EOL;
}

echo PHP_EOL;
echo "Now run: php artisan license:check --force" . PHP_EOL;
echo "This will fetch fresh data from LICENSE-MANAGER" . PHP_EOL;
