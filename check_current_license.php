<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== POS-RESTO LICENSE CONFIGURATION ===" . PHP_EOL . PHP_EOL;

echo "LICENSE_SERVER_URL: " . config('license.server_url') . PHP_EOL;
echo "LICENSE_KEY: " . config('license.license_key') . PHP_EOL;
echo PHP_EOL;

// Try to get license info
$service = new \App\Services\LicenseManagerService();
$licenseInfo = $service->getLicenseInfo();

if ($licenseInfo) {
    echo "Current License Info:" . PHP_EOL;
    print_r($licenseInfo);
} else {
    echo "No license info available." . PHP_EOL;
}

echo PHP_EOL;
echo "Checking cache..." . PHP_EOL;
$cacheKey = config('license.cache_key', 'pos_resto_license_data');
if (Cache::has($cacheKey)) {
    echo "Cached license data:" . PHP_EOL;
    print_r(Cache::get($cacheKey));
} else {
    echo "No cached license data." . PHP_EOL;
}
