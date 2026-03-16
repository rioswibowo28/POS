<?php

namespace App\Repositories;

use App\Models\Table;
use App\Enums\TableStatus;

class TableRepository extends BaseRepository
{
    public function __construct(Table $model)
    {
        parent::__construct($model);
    }

    public function query()
    {
        return $this->model->newQuery();
    }

    public function where($column, $operator = null, $value = null)
    {
        return $this->model->where($column, $operator, $value);
    }

    public function getAvailable()
    {
        return $this->model->where('status', TableStatus::AVAILABLE)
            ->where('is_active', true)
            ->get();
    }

    public function getOccupied()
    {
        return $this->model->where('status', TableStatus::OCCUPIED)
            ->with('currentOrder')
            ->get();
    }

    public function updateStatus($id, TableStatus|string $status)
    {
        $table = $this->findOrFail($id);
        $table->status = $status; // Will be cast to enum by model
        $table->save();
        return $table;
    }
}
