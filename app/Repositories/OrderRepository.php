<?php

namespace App\Repositories;

use App\Models\Order;
use App\Enums\OrderStatus;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function getActive()
    {
        return $this->model->whereIn('status', [OrderStatus::PENDING, OrderStatus::PROCESSING])
            ->with(['table', 'items.product', 'items.variant'])
            ->get();
    }

    public function getByTable($tableId)
    {
        return $this->model->where('table_id', $tableId)
            ->whereIn('status', [OrderStatus::PENDING, OrderStatus::PROCESSING])
            ->with(['items.product', 'items.variant'])
            ->first();
    }

    public function getByOrderNumber($orderNumber)
    {
        return $this->model->where('order_number', $orderNumber)
            ->with(['table', 'items.product', 'items.variant', 'payments'])
            ->first();
    }

    public function getTodayOrders()
    {
        return $this->model->whereDate('created_at', today())
            ->with(['table', 'items', 'payments'])
            ->get();
    }

    public function getCompletedOrders($startDate = null, $endDate = null)
    {
        $query = $this->model->where('status', OrderStatus::COMPLETED);

        if ($startDate) {
            $query->whereDate('completed_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('completed_at', '<=', $endDate);
        }

        return $query->with(['table', 'items', 'payments'])->get();
    }
}
