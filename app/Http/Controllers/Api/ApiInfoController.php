<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ApiInfoController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'POS Resto API',
            'version' => '1.0.0',
            'endpoints' => [
                'auth' => [
                    'POST /api/login' => 'Login user',
                    'POST /api/register' => 'Register user baru',
                    'POST /api/logout' => 'Logout (requires token)',
                    'GET /api/me' => 'Get user profile (requires token)',
                    'POST /api/refresh' => 'Refresh token (requires token)',
                ],
                'categories' => [
                    'GET /api/categories' => 'Get all categories',
                    'GET /api/categories/active' => 'Get active categories',
                    'POST /api/categories' => 'Create category',
                    'GET /api/categories/{id}' => 'Get category by ID',
                    'PUT /api/categories/{id}' => 'Update category',
                    'DELETE /api/categories/{id}' => 'Delete category',
                ],
                'products' => [
                    'GET /api/products' => 'Get all products',
                    'GET /api/products/available' => 'Get available products',
                    'GET /api/products/category/{categoryId}' => 'Get products by category',
                    'POST /api/products/search' => 'Search products',
                    'POST /api/products' => 'Create product',
                    'GET /api/products/{id}' => 'Get product by ID',
                    'PUT /api/products/{id}' => 'Update product',
                    'DELETE /api/products/{id}' => 'Delete product',
                ],
                'tables' => [
                    'GET /api/tables' => 'Get all tables',
                    'GET /api/tables/available' => 'Get available tables',
                    'GET /api/tables/occupied' => 'Get occupied tables',
                    'POST /api/tables' => 'Create table',
                    'GET /api/tables/{id}' => 'Get table by ID',
                    'PUT /api/tables/{id}' => 'Update table',
                    'PUT /api/tables/{id}/status' => 'Update table status',
                    'DELETE /api/tables/{id}' => 'Delete table',
                ],
                'orders' => [
                    'GET /api/orders' => 'Get active orders',
                    'GET /api/orders/today' => 'Get today orders',
                    'GET /api/orders/completed' => 'Get completed orders',
                    'GET /api/orders/table/{tableId}' => 'Get order by table',
                    'GET /api/orders/number/{orderNumber}' => 'Get order by number',
                    'POST /api/orders' => 'Create order',
                    'POST /api/orders/{orderId}/items' => 'Add item to order',
                    'PUT /api/orders/items/{itemId}' => 'Update order item',
                    'DELETE /api/orders/items/{itemId}' => 'Remove order item',
                    'PUT /api/orders/{orderId}/status' => 'Update order status',
                    'POST /api/orders/{orderId}/cancel' => 'Cancel order',
                ],
                'payments' => [
                    'GET /api/payments/order/{orderId}' => 'Get payments by order',
                    'GET /api/payments/today' => 'Get today payments',
                    'GET /api/payments/number/{paymentNumber}' => 'Get payment by number',
                    'POST /api/payments/process' => 'Process payment',
                    'POST /api/payments/{paymentId}/void' => 'Void payment',
                ],
            ],
            'authentication' => [
                'type' => 'JWT Bearer Token',
                'header' => 'Authorization: Bearer {token}',
                'example' => [
                    'email' => 'admin@posresto.com',
                    'password' => 'password',
                ],
            ],
            'test_endpoints' => [
                'login' => url('/api/login'),
                'categories' => url('/api/categories'),
                'products' => url('/api/products'),
                'tables' => url('/api/tables'),
            ],
        ], 200);
    }
}
