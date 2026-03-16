<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Enums\PaymentStatus;

class PaymentRepository extends BaseRepository
{
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    public function getByOrder($orderId)
    {
        return $this->model->where('order_id', $orderId)->get();
    }

    public function getTodayPayments()
    {
        return $this->model->whereDate('created_at', today())
            ->where('status', PaymentStatus::COMPLETED)
            ->with('order')
            ->get();
    }

    public function getByPaymentNumber($paymentNumber)
    {
        return $this->model->where('payment_number', $paymentNumber)
            ->with('order')
            ->first();
    }
}
