<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== POS-RESTO LICENSE INFO ===" . PHP_EOL . PHP_EOL;

// Check cache
$cacheKey = 'license_verification';
if (Cache::has($cacheKey)) {
    echo "License data in cache:" . PHP_EOL;
    $cachedData = Cache::get($cacheKey);
    print_r($cachedData);
} else {
    echo "No license data in cache" . PHP_EOL;
}

echo PHP_EOL;

// Check config
echo "License Server URL: " . config('license.server_url') . PHP_EOL;
echo "License Key: " . config('license.key') . PHP_EOL;
