<?php

use App\Services\MidtransService;
use App\Models\Setting;

echo "=== Midtrans Configuration Test ===\n\n";

echo "1. Checking Settings:\n";
echo "   Server Key: " . (Setting::get('midtrans_server_key') ? 'SET ✓' : 'NOT SET ✗') . "\n";
echo "   Client Key: " . (Setting::get('midtrans_client_key') ? 'SET ✓' : 'NOT SET ✗') . "\n";
echo "   Merchant ID: " . (Setting::get('midtrans_merchant_id') ? 'SET ✓' : 'NOT SET ✗') . "\n";
echo "   Is Production: " . Setting::get('midtrans_is_production', '0') . "\n\n";

echo "2. Midtrans Service Status:\n";
echo "   Is Configured: " . (MidtransService::isConfigured() ? 'YES ✓' : 'NO ✗') . "\n\n";

try {
    $service = new MidtransService();
    echo "3. Service Initialization: SUCCESS ✓\n\n";
    
    // Test create snap token with dummy data
    echo "4. Testing Snap Token Generation...\n";
    $testData = [
        'order_number' => 'TEST-' . time(),
        'total' => 50000,
        'customer_name' => 'Test Customer',
        'customer_email' => 'test@example.com',
        'customer_phone' => '08123456789',
        'items' => [
            [
                'product_id' => 1,
                'name' => 'Test Product',
                'price' => 50000,
                'quantity' => 1
            ]
        ]
    ];
    
    $snapToken = $service->createSnapToken($testData);
    echo "   Snap Token: " . substr($snapToken, 0, 30) . "... ✓\n";
    echo "   Token Length: " . strlen($snapToken) . "\n\n";
    
    echo "=== ALL TESTS PASSED ✓ ===\n";
    
} catch (\Exception $e) {
    echo "3. Service Initialization: FAILED ✗\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
    echo "=== TEST FAILED ✗ ===\n";
}
