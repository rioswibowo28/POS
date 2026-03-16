<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\POSController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\ShiftController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\TableController;
use App\Http\Controllers\Web\SettingController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\MidtransController;
use App\Http\Controllers\Web\PackageController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\Web\BackupController;

// License routes (no auth or license check required)
Route::get('/license/activate', [LicenseController::class, 'showActivation'])->name('license.activate');
Route::post('/license/activate', [LicenseController::class, 'activate'])->name('license.activate.process');
Route::post('/license/lookup', [LicenseController::class, 'lookup'])->name('license.lookup');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Public routes (no auth required)
Route::get('/pos/customer-display', [POSController::class, 'customerDisplay'])->name('pos.customerDisplay');
Route::get('/api/customer-display/data', [POSController::class, 'getCustomerDisplayData'])->name('pos.getCustomerDisplayData');
Route::post('/api/customer-display/data', [POSController::class, 'updateCustomerDisplayData'])->name('pos.updateCustomerDisplayData');

// Authenticated routes
Route::middleware(['auth', 'check.license'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // License Info
    Route::get('/license/info', [LicenseController::class, 'info'])->name('license.info');
    Route::post('/license/update', [LicenseController::class, 'updateLicense'])->name('license.update');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // POS/Kasir
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::post('/pos/create-order', [POSController::class, 'createOrder'])->name('pos.createOrder');
    
    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}/edit', [OrderController::class, 'edit'])->name('orders.edit');
    Route::put('/orders/{id}', [OrderController::class, 'update'])->name('orders.update');
    Route::get('/orders/{id}/payment', [PaymentController::class, 'show'])->name('orders.payment');
    Route::post('/orders/{id}/payment', [PaymentController::class, 'process']);
    Route::get('/orders/{id}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    
    // Shifts
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::get('/shifts/create', [ShiftController::class, 'create'])->name('shifts.create');
    Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
    Route::get('/shifts/{shift}', [ShiftController::class, 'show'])->name('shifts.show');
    Route::get('/shifts/{shift}/close', [ShiftController::class, 'closeForm'])->name('shifts.closeForm');
    Route::post('/shifts/{shift}/close', [ShiftController::class, 'close'])->name('shifts.close');
    Route::get('/shifts/{shift}/print', [ShiftController::class, 'print'])->name('shifts.print');
    
    // Products (Admin Only)
    Route::middleware('admin')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        
        // Products Bulk Operations (harus sebelum route {id} wildcard)
        Route::post('/products/bulk-store', [ProductController::class, 'bulkStore'])->name('products.bulkStore');
        Route::put('/products/bulk-update', [ProductController::class, 'bulkUpdate'])->name('products.bulkUpdate');
        Route::delete('/products/bulk-destroy', [ProductController::class, 'bulkDestroy'])->name('products.bulkDestroy');

        Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::get('/products/{id}/stock', [ProductController::class, 'getStock'])->name('products.stock');
        Route::post('/products/{id}/stock', [ProductController::class, 'updateStock'])->name('products.updateStock');
    });
    
    // Categories (Admin Only)
    Route::middleware('admin')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });
    
    // Tables (Admin Only)
    Route::middleware('admin')->group(function () {
        Route::get('/tables', [TableController::class, 'index'])->name('tables.index');
        Route::post('/tables', [TableController::class, 'store'])->name('tables.store');
        Route::get('/tables/{id}/edit', [TableController::class, 'edit'])->name('tables.edit');
        Route::put('/tables/{id}', [TableController::class, 'update'])->name('tables.update');
        Route::delete('/tables/{id}', [TableController::class, 'destroy'])->name('tables.destroy');
    });

    // Backup (Admin Only)
    Route::middleware('admin')->group(function () {
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'create'])->name('backups.create');
        Route::get('/backups/download/{filename}', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');
    });

    // Packages (Admin Only)
    Route::middleware('admin')->group(function () {
        Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
        Route::post('/packages', [PackageController::class, 'store'])->name('packages.store');
        Route::get('/packages/{id}/edit', [PackageController::class, 'edit'])->name('packages.edit');
        Route::put('/packages/{id}', [PackageController::class, 'update'])->name('packages.update');
        Route::patch('/packages/{id}/toggle-active', [PackageController::class, 'toggleActive'])->name('packages.toggleActive');
        Route::delete('/packages/{id}', [PackageController::class, 'destroy'])->name('packages.destroy');
    });
    
    // Users (Admin Only)
    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
    });
    
    // Reports (Admin & Cashier)
    Route::middleware('admin.or.cashier')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/tax-sales', [ReportController::class, 'taxSales'])->name('reports.tax-sales');
        Route::get('/reports/tax-sales/print', [ReportController::class, 'taxSalesPrint'])->name('reports.tax-sales.print');
        Route::get('/reports/tax-sales/recap-print', [ReportController::class, 'taxSalesRecapPrint'])->name('reports.tax-sales.recap-print');
        Route::get('/reports/tax-sales/export-excel', [ReportController::class, 'taxSalesExportExcel'])->name('reports.tax-sales.export-excel');
        Route::get('/reports/tax-sales/export-pdf', [ReportController::class, 'taxSalesExportPdf'])->name('reports.tax-sales.export-pdf');
        Route::get('/reports/tax-sales/recap-export-pdf', [ReportController::class, 'taxSalesRecapExportPdf'])->name('reports.tax-sales.recap-export-pdf');
    });

    // Reports Internal Revenue (Admin Only)
    Route::middleware('admin')->group(function () {
        Route::get('/reports/internal-revenue', [ReportController::class, 'internalRevenue'])->name('reports.internal-revenue');
        Route::get('/reports/internal-revenue/print', [ReportController::class, 'internalRevenuePrint'])->name('reports.internal-revenue.print');
        Route::get('/reports/internal-revenue/export-excel', [ReportController::class, 'internalRevenueExportExcel'])->name('reports.internal-revenue.export-excel');
        Route::get('/reports/internal-revenue/export-pdf', [ReportController::class, 'internalRevenueExportPdf'])->name('reports.internal-revenue.export-pdf');
    });
    
    // Settings (Admin Only)
    Route::middleware('admin')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/admin/test-license-connection', [SettingController::class, 'testLicenseConnection'])->name('settings.testLicense');
        Route::post('/admin/settings/remove-logo', [SettingController::class, 'removeLogo'])->name('settings.removeLogo');
        Route::post('/admin/settings/remove-poster', [SettingController::class, 'removePoster'])->name('settings.removePoster');
    });
    
    // Admin: License Management - DISABLED (managed in LICENSE-MANAGER)
    // Route::prefix('admin/licenses')->name('license.')->group(function () {
    //     Route::get('/', [LicenseController::class, 'index'])->name('index');
    //     Route::get('/create', [LicenseController::class, 'create'])->name('create');
    //     Route::post('/', [LicenseController::class, 'store'])->name('store');
    //     Route::get('/{license}', [LicenseController::class, 'show'])->name('show');
    //     Route::post('/{license}/suspend', [LicenseController::class, 'suspend'])->name('suspend');
    //     Route::post('/{license}/renew', [LicenseController::class, 'renew'])->name('renew');
    //     Route::delete('/{license}', [LicenseController::class, 'destroy'])->name('destroy');
    // });
    
    // Midtrans
    Route::get('/midtrans/status/{orderId}', [MidtransController::class, 'checkStatus'])->name('midtrans.status');
    
    // Test Midtrans (only for development)
    Route::get('/test-midtrans', function() {
        $output = [];
        
        $output[] = "=== Midtrans Configuration Test ===\n";
        
        $output[] = "1. Checking Settings:";
        $output[] = "   Server Key: " . (\App\Models\Setting::get('midtrans_server_key') ? 'SET ✓' : 'NOT SET ✗');
        $output[] = "   Client Key: " . (\App\Models\Setting::get('midtrans_client_key') ? 'SET ✓' : 'NOT SET ✗');
        $output[] = "   Is Production: " . \App\Models\Setting::get('midtrans_is_production', '0') . "\n";
        
        $output[] = "2. Midtrans Service Status:";
        $output[] = "   Is Configured: " . (\App\Services\MidtransService::isConfigured() ? 'YES ✓' : 'NO ✗') . "\n";
        
        try {
            $service = new \App\Services\MidtransService();
            $output[] = "3. Service Initialization: SUCCESS ✓\n";
            
            $output[] = "4. Testing Snap Token Generation...";
            $testData = [
                'order_number' => 'TEST-' . time(),
                'total' => 50000,
                'customer_name' => 'Test Customer',
                'customer_email' => 'test@example.com',
                'customer_phone' => '08123456789',
                'items' => [[
                    'product_id' => 1,
                    'name' => 'Test Product',
                    'price' => 50000,
                    'quantity' => 1
                ]]
            ];
            
            $snapToken = $service->createSnapToken($testData);
            $output[] = "   Snap Token: " . substr($snapToken, 0, 30) . "... ✓";
            $output[] = "   Token Length: " . strlen($snapToken) . "\n";
            
            $output[] = "=== ALL TESTS PASSED ✓ ===";
            
        } catch (\Exception $e) {
            $output[] = "3. Service Initialization: FAILED ✗";
            $output[] = "   Error: " . $e->getMessage() . "\n";
            $output[] = "=== TEST FAILED ✗ ===";
        }
        
        return '<pre>' . implode("\n", $output) . '</pre>';
    });
});

// Midtrans Notification (no auth required)
Route::post('/midtrans/notification', [MidtransController::class, 'notification'])->name('midtrans.notification');
