<?php

namespace App\Http\Controllers\Api;

use App\Services\PaymentService;
use App\Models\Payment;
use App\Models\Order;
use App\Enums\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends BaseController
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Get payments by order (flag=0 only)
     */
    public function byOrder($orderId)
    {
        try {
            // Verify order is flag=0
            $order = Order::where('id', $orderId)->where('flag', false)->first();
            if (!$order) {
                return $this->sendError('Order not found', [], 404);
            }
            $payments = $this->paymentService->getPaymentsByOrder($orderId);
            return $this->sendResponse($payments, 'Payments retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving payments', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get today's payments (flag=0 orders only)
     */
    public function today()
    {
        try {
            $payments = Payment::whereHas('order', function ($q) {
                    $q->where('flag', false);
                })
                ->whereDate('created_at', today())
                ->with(['order'])
                ->get();
            return $this->sendResponse($payments, 'Today payments retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving today payments', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get payment by number (flag=0 orders only)
     */
    public function byPaymentNumber($paymentNumber)
    {
        try {
            $payment = Payment::whereHas('order', function ($q) {
                    $q->where('flag', false);
                })
                ->where('payment_number', $paymentNumber)
                ->with(['order'])
                ->first();

            if (!$payment) {
                return $this->sendError('Payment not found', [], 404);
            }

            return $this->sendResponse($payment, 'Payment retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Payment not found', ['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Process payment (flag=0 orders only via API)
     */
    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'method' => 'required|in:cash,qris,bank_transfer',
            'amount' => 'required|numeric|min:0',
            'received_amount' => 'nullable|numeric|min:0',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            // Verify order is flag=0 before processing payment via API
            $order = Order::where('id', $request->order_id)->where('flag', false)->first();
            if (!$order) {
                return $this->sendError('Order not found', [], 404);
            }

            $payment = $this->paymentService->processPayment($request->all());
            return $this->sendResponse($payment, 'Payment processed successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError('Error processing payment', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Void payment (flag=0 orders only via API)
     */
    public function void($paymentId)
    {
        try {
            // Verify the payment belongs to a flag=0 order
            $payment = Payment::whereHas('order', function ($q) {
                    $q->where('flag', false);
                })
                ->where('id', $paymentId)
                ->first();

            if (!$payment) {
                return $this->sendError('Payment not found', [], 404);
            }

            $result = $this->paymentService->voidPayment($paymentId);
            return $this->sendResponse($result, 'Payment voided successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error voiding payment', ['error' => $e->getMessage()], 500);
        }
    }
}
