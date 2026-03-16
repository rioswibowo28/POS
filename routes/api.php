<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ApiInfoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API Info - Root endpoint
Route::get('/', [ApiInfoController::class, 'index']);

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:api', 'api.audit', 'tax.readonly'])->group(function () {
    
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);

    // Category routes
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/active', [CategoryController::class, 'active']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });

    // Product routes
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/available', [ProductController::class, 'available']);
        Route::get('/category/{categoryId}', [ProductController::class, 'byCategory']);
        Route::post('/search', [ProductController::class, 'search']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
    });

    // Table routes
    Route::prefix('tables')->group(function () {
        Route::get('/', [TableController::class, 'index']);
        Route::get('/available', [TableController::class, 'available']);
        Route::get('/occupied', [TableController::class, 'occupied']);
        Route::post('/', [TableController::class, 'store']);
        Route::get('/{id}', [TableController::class, 'show']);
        Route::put('/{id}', [TableController::class, 'update']);
        Route::put('/{id}/status', [TableController::class, 'updateStatus']);
        Route::delete('/{id}', [TableController::class, 'destroy']);
    });

    // Order routes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/today', [OrderController::class, 'today']);
        Route::get('/completed', [OrderController::class, 'completed']);
        Route::get('/table/{tableId}', [OrderController::class, 'byTable']);
        Route::get('/number/{orderNumber}', [OrderController::class, 'byOrderNumber']);
        Route::post('/', [OrderController::class, 'store']);
        Route::post('/{orderId}/items', [OrderController::class, 'addItem']);
        Route::put('/items/{itemId}', [OrderController::class, 'updateItem']);
        Route::delete('/items/{itemId}', [OrderController::class, 'removeItem']);
        Route::put('/{orderId}/status', [OrderController::class, 'updateStatus']);
        Route::post('/{orderId}/cancel', [OrderController::class, 'cancel']);
    });

    // Payment routes
    Route::prefix('payments')->group(function () {
        Route::get('/order/{orderId}', [PaymentController::class, 'byOrder']);
        Route::get('/today', [PaymentController::class, 'today']);
        Route::get('/number/{paymentNumber}', [PaymentController::class, 'byPaymentNumber']);
        Route::post('/process', [PaymentController::class, 'process']);
        Route::post('/{paymentId}/void', [PaymentController::class, 'void']);
    });
});
