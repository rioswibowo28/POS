<?php

namespace App\Http\Controllers\Api;

use App\Services\TableService;
use App\Enums\TableStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TableController extends BaseController
{
    protected $tableService;

    public function __construct(TableService $tableService)
    {
        $this->tableService = $tableService;
    }

    public function index()
    {
        try {
            $tables = $this->tableService->getAllTables();
            return $this->sendResponse($tables, 'Tables retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving tables', ['error' => $e->getMessage()], 500);
        }
    }

    public function available()
    {
        try {
            $tables = $this->tableService->getAvailableTables();
            return $this->sendResponse($tables, 'Available tables retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving available tables', ['error' => $e->getMessage()], 500);
        }
    }

    public function occupied()
    {
        try {
            $tables = $this->tableService->getOccupiedTables();
            return $this->sendResponse($tables, 'Occupied tables retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving occupied tables', ['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|string|max:50|unique:tables,number',
            'capacity' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $table = $this->tableService->createTable($request->all());
            return $this->sendResponse($table, 'Table created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError('Error creating table', ['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $table = $this->tableService->getTableById($id);
            return $this->sendResponse($table, 'Table retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Table not found', ['error' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'string|max:50|unique:tables,number,' . $id,
            'capacity' => 'integer|min:1',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $table = $this->tableService->updateTable($id, $request->all());
            return $this->sendResponse($table, 'Table updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error updating table', ['error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:available,occupied,reserved',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $status = TableStatus::from($request->status);
            $table = $this->tableService->updateTableStatus($id, $status);
            return $this->sendResponse($table, 'Table status updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error updating table status', ['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->tableService->deleteTable($id);
            return $this->sendResponse([], 'Table deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error deleting table', ['error' => $e->getMessage()], 500);
        }
    }
}
