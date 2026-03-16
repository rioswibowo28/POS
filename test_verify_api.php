<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST LICENSE VERIFICATION ===" . PHP_EOL . PHP_EOL;

$licenseKey = config('license.license_key');
$serverUrl = rtrim(config('license.server_url'), '/');

echo "License Key: " . $licenseKey . PHP_EOL;
echo "Server URL: " . $serverUrl . PHP_EOL;
echo PHP_EOL;

// Call verify API directly
echo "Calling verify API..." . PHP_EOL;

try {
    $response = \Illuminate\Support\Facades\Http::timeout(10)
        ->post("{$serverUrl}/api/license/verify", [
            'license_key' => $licenseKey,
        ]);
    
    echo "Response Status: " . $response->status() . PHP_EOL;
    echo "Response Body:" . PHP_EOL;
    print_r($response->json());
    
    if ($response->successful()) {
        $data = $response->json();
        if ($data['valid'] ?? false) {
            echo PHP_EOL . "✅ License is VALID!" . PHP_EOL;
            echo "Expiry Date: " . ($data['license']['expiry_date'] ?? 'N/A') . PHP_EOL;
            echo "Days Remaining: " . ($data['license']['days_remaining'] ?? 'N/A') . PHP_EOL;
        } else {
            echo PHP_EOL . "❌ License is INVALID" . PHP_EOL;
            echo "Message: " . ($data['message'] ?? 'Unknown error') . PHP_EOL;
        }
    } else {
        echo PHP_EOL . "❌ API call failed" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
