<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Testing Table Sorting ===\n\n";

// Get tables with current sorting
$tables = DB::table('tables')
    ->whereNull('deleted_at')
    ->orderByRaw('CAST(number AS UNSIGNED) ASC, number ASC')
    ->get(['id', 'number', 'capacity', 'status']);

echo "Tables sorted by number (numeric then alphabetic):\n";
echo str_repeat('-', 60) . "\n";
printf("%-5s %-15s %-10s %-15s\n", "ID", "Number", "Capacity", "Status");
echo str_repeat('-', 60) . "\n";

foreach ($tables as $table) {
    printf("%-5s %-15s %-10s %-15s\n", 
        $table->id, 
        $table->number, 
        $table->capacity, 
        $table->status
    );
}

echo "\n=== Expected Order ===\n";
echo "Numeric tables: 1, 2, 3, 4, ... 10, 11, 12 ... 20\n";
echo "Alphanumeric tables: A1, A10, A2, B1, VIP1, etc.\n";
echo "\nTest complete!\n";
