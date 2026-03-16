<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getAvailable()
    {
        return $this->model->where('is_available', true)
            ->where('is_active', true)
            ->with(['category', 'variants', 'modifiers'])
            ->get();
    }

    public function getByCategory($categoryId)
    {
        return $this->model->where('category_id', $categoryId)
            ->where('is_active', true)
            ->with(['variants', 'modifiers'])
            ->get();
    }

    public function search($keyword)
    {
        return $this->model->where(function ($query) use ($keyword) {
            $query->where('name', 'like', "%{$keyword}%")
                ->orWhere('sku', 'like', "%{$keyword}%")
                ->orWhere('barcode', 'like', "%{$keyword}%");
        })->with(['category', 'variants', 'modifiers'])->get();
    }

    public function withRelations()
    {
        return $this->model->with(['category', 'variants', 'modifiers', 'inventory'])->get();
    }
}
