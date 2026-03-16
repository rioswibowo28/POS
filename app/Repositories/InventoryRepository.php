<?php

namespace App\Repositories;

use App\Models\Inventory;

class InventoryRepository extends BaseRepository
{
    public function __construct(Inventory $model)
    {
        parent::__construct($model);
    }

    public function getLowStock()
    {
        return $this->model->whereRaw('quantity <= min_quantity')
            ->with('product')
            ->get();
    }

    public function getByProduct($productId)
    {
        return $this->model->where('product_id', $productId)->first();
    }

    public function updateQuantity($productId, $quantity)
    {
        $inventory = $this->getByProduct($productId);
        if ($inventory) {
            $inventory->quantity += $quantity;
            $inventory->save();
        }
        return $inventory;
    }
}
