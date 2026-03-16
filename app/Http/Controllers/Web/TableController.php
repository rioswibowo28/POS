<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\TableRepository;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function __construct(
        private TableRepository $tableRepository
    ) {}

    public function index(Request $request)
    {
        $query = $this->tableRepository->query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query = $query->where('number', 'like', '%' . $search . '%');
        }

        // Status filter
        if ($request->filled('status')) {
            $query = $query->where('status', $request->status);
        }

        // Sort by number (numeric sorting for numbers, alphabetic for mixed)
        $tables = $query->orderByRaw('CAST(number AS UNSIGNED) ASC, number ASC')->paginate(20);

        return view('tables.index', compact('tables'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|unique:tables,number',
            'capacity' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['status'] = 'available';

        $this->tableRepository->create($validated);

        return redirect()->route('tables.index')->with('success', 'Table created successfully');
    }

    public function edit($id)
    {
        $table = $this->tableRepository->findOrFail($id);
        return view('tables.edit', compact('table'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'number' => 'required|string|unique:tables,number,' . $id,
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,reserved,cleaning',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $this->tableRepository->update($id, $validated);

        return redirect()->route('tables.index')->with('success', 'Table updated successfully');
    }

    public function destroy($id)
    {
        $this->tableRepository->delete($id);
        return redirect()->route('tables.index')->with('success', 'Table deleted successfully');
    }
}
