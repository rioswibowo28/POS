<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST DAYS CALCULATION IN POS-RESTO ===" . PHP_EOL . PHP_EOL;

// Test with different dates
$testCases = [
    ['activation' => '2026-02-23', 'expiry' => '2026-03-25', 'expected' => 30, 'desc' => 'Monthly from Feb 23'],
    ['activation' => '2026-02-23', 'expiry' => '2026-03-02', 'expected' => 7, 'desc' => 'Trial from Feb 23'],
    ['activation' => '2026-01-15', 'expiry' => '2026-02-14', 'expected' => -8, 'desc' => 'Expired license'],
];

foreach ($testCases as $i => $test) {
    echo ($i + 1) . ". " . $test['desc'] . PHP_EOL;
    echo "   Activation: " . $test['activation'] . PHP_EOL;
    echo "   Expiry: " . $test['expiry'] . PHP_EOL;
    
    // Simulate the calculation
    $expiryDate = $test['expiry'];
    
    // Set to start of day for today
    $now = new \DateTime('2026-02-23'); // Today
    $now->setTime(0, 0, 0);
    
    // Set to end of day for expiry to include the expiry date
    $expiry = new \DateTime($expiryDate);
    $expiry->setTime(23, 59, 59);
    
    $diff = $now->diff($expiry);
    
    // Calculate days including the expiry date itself
    $days = (int) ceil(($expiry->getTimestamp() - $now->getTimestamp()) / (60 * 60 * 24));
    
    $result = $diff->invert ? -abs($days) : $days;
    
    echo "   Result: " . $result . " days";
    echo " (Expected: " . $test['expected'] . ")";
    echo ($result == $test['expected'] ? " ✅" : " ❌") . PHP_EOL;
    echo PHP_EOL;
}

echo "=== TEST REAL CALCULATION METHOD ===" . PHP_EOL . PHP_EOL;

// Test the actual method
$service = new \App\Services\LicenseManagerService();
$reflection = new ReflectionClass($service);
$method = $reflection->getMethod('calculateDaysRemaining');
$method->setAccessible(true);

$result1 = $method->invoke($service, '2026-03-25');
echo "Days remaining for 2026-03-25: " . $result1 . " (Expected: 30)" . PHP_EOL;

$result2 = $method->invoke($service, '2026-03-02');
echo "Days remaining for 2026-03-02: " . $result2 . " (Expected: 7)" . PHP_EOL;

$result3 = $method->invoke($service, null);
echo "Days remaining for null (lifetime): " . ($result3 === null ? 'null' : $result3) . " (Expected: null)" . PHP_EOL;
