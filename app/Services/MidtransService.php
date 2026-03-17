<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use App\Models\Setting;

class MidtransService
{
    public function __construct()
    {
        $this->initConfig();
    }

    private function initConfig()
    {
        // Set Midtrans configuration
        $serverKey = Setting::get('midtrans_server_key');
        $clientKey = Setting::get('midtrans_client_key');
        $isProduction = (bool) Setting::get('midtrans_is_production', '0');
        
        if (empty($serverKey) || empty($clientKey)) {
            \Log::warning('Midtrans Configuration Missing', [
                'server_key_exists' => !empty($serverKey),
                'client_key_exists' => !empty($clientKey)
            ]);
            return; // Don't throw immediately, only fail when actually creating tokens
        }
        
        Config::$serverKey = $serverKey;
        Config::$clientKey = $clientKey;
        Config::$isProduction = $isProduction;
        Config::$isSanitized = true;
        Config::$is3ds = true;
        
        \Log::info('Midtrans Config Initialized', [
            'is_production' => $isProduction,
            'client_key' => substr($clientKey, 0, 10) . '...'
        ]);
    }

    /**
     * Create Snap Token for payment
     * 
     * @param array $orderData
     * @return string Snap Token
     */
    public function createSnapToken($orderData)
    {
        if (empty(Config::$serverKey)) {
            throw new \Exception('Midtrans configuration is incomplete. Please check your system settings.');
        }

        // Add timestamp to order_id to make it unique for each payment attempt
        $uniqueOrderId = $orderData['order_number'] . '-' . time();
        
        $params = [
            'transaction_details' => [
                'order_id' => $uniqueOrderId,
                'gross_amount' => (int) $orderData['total'],
            ],
            'customer_details' => [
                'first_name' => $orderData['customer_name'] ?? 'Customer',
                'email' => $orderData['customer_email'] ?? Setting::get('restaurant_email', 'info@posresto.com'),
                'phone' => $orderData['customer_phone'] ?? Setting::get('restaurant_phone', '021-12345678'),
            ],
            'item_details' => $this->formatItemDetails($orderData['items']),
        ];

        \Log::info('Creating Midtrans Snap Token', [
            'order_id' => $params['transaction_details']['order_id'],
            'amount' => $params['transaction_details']['gross_amount']
        ]);

        try {
            $snapToken = Snap::getSnapToken($params);
            
            \Log::info('Snap Token Created Successfully', [
                'order_id' => $params['transaction_details']['order_id'],
                'token' => substr($snapToken, 0, 20) . '...'
            ]);
            
            return $snapToken;
        } catch (\Exception $e) {
            \Log::error('Midtrans Snap Token Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'params' => $params
            ]);
            throw new \Exception('Failed to create payment: ' . $e->getMessage());
        }
    }

    /**
     * Format order items for Midtrans
     */
    private function formatItemDetails($items)
    {
        $itemDetails = [];
        
        foreach ($items as $item) {
            $itemDetails[] = [
                'id' => $item['product_id'],
                'price' => (int) $item['price'],
                'quantity' => (int) $item['quantity'],
                'name' => $item['name'],
            ];
        }

        return $itemDetails;
    }

    /**
     * Get transaction status
     * 
     * @param string $orderId
     * @return object
     */
    public function getTransactionStatus($orderId)
    {
        try {
            $status = Transaction::status($orderId);
            return $status;
        } catch (\Exception $e) {
            \Log::error('Midtrans Get Status Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify notification from Midtrans
     * 
     * @return object
     */
    public function handleNotification()
    {
        try {
            $notification = new \Midtrans\Notification();
            
            return [
                'order_id' => $notification->order_id,
                'status_code' => $notification->status_code,
                'gross_amount' => $notification->gross_amount,
                'transaction_status' => $notification->transaction_status,
                'transaction_id' => $notification->transaction_id,
                'fraud_status' => $notification->fraud_status ?? null,
                'payment_type' => $notification->payment_type,
            ];
        } catch (\Exception $e) {
            \Log::error('Midtrans Notification Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if Midtrans is configured and enabled
     * 
     * @return bool
     */
    public static function isConfigured()
    {
        // First check if Midtrans is enabled
        $enabled = (bool) Setting::get('midtrans_enabled', '0');
        
        if (!$enabled) {
            return false;
        }
        
        // Then check if credentials are set
        $serverKey = Setting::get('midtrans_server_key');
        $clientKey = Setting::get('midtrans_client_key');
        
        return !empty($serverKey) && !empty($clientKey);
    }
}
