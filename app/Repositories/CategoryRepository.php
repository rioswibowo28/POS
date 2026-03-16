<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository extends BaseRepository
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function getActive()
    {
        return $this->model->where('is_active', true)->get();
    }

    public function withCount($relation)
    {
        return $this->model->withCount($relation);
    }

    public function withProducts()
    {
        return $this->model->with('products')->get();
    }
}
