<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;

echo "=== Testing Bill Number Generation ===\n\n";

// Test flag=0 (Normal order)
$billNumber0 = Order::generateBillNumber(false);
echo "Flag = 0 (Normal): " . $billNumber0 . "\n";
echo "Expected format: BILL-260304-0001 (4 digits)\n\n";

// Test flag=1 (No Tax order)
$billNumber1 = Order::generateBillNumber(true);
echo "Flag = 1 (No Tax): " . $billNumber1 . "\n";
echo "Expected format: BILL-260304-001 (3 digits)\n\n";

echo "=== Testing Order Number Generation ===\n\n";

// Test order number flag=0
$orderNumber0 = Order::generateOrderNumber(false);
echo "Flag = 0 (Normal): " . $orderNumber0 . "\n";
echo "Expected format: ORD-20260304-0001 (4 digits)\n\n";

// Test order number flag=1
$orderNumber1 = Order::generateOrderNumber(true);
echo "Flag = 1 (No Tax): " . $orderNumber1 . "\n";
echo "Expected format: ORD-20260304-001 (3 digits)\n\n";

echo "=== Test Complete ===\n";
