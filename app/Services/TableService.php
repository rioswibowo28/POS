<?php

namespace App\Services;

use App\Repositories\TableRepository;
use App\Enums\TableStatus;

class TableService
{
    protected $tableRepository;

    public function __construct(TableRepository $tableRepository)
    {
        $this->tableRepository = $tableRepository;
    }

    public function getAllTables()
    {
        return $this->tableRepository->all();
    }

    public function getAvailableTables()
    {
        return $this->tableRepository->getAvailable();
    }

    public function getOccupiedTables()
    {
        return $this->tableRepository->getOccupied();
    }

    public function getTableById($id)
    {
        return $this->tableRepository->findOrFail($id);
    }

    public function createTable(array $data)
    {
        $data['status'] = TableStatus::AVAILABLE;
        return $this->tableRepository->create($data);
    }

    public function updateTable($id, array $data)
    {
        return $this->tableRepository->update($id, $data);
    }

    public function deleteTable($id)
    {
        return $this->tableRepository->delete($id);
    }

    public function updateTableStatus($id, TableStatus $status)
    {
        return $this->tableRepository->updateStatus($id, $status);
    }
}
