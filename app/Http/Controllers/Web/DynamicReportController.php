<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DynamicReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\DynamicDataExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class DynamicReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userRole = $user->role;

        $query = DynamicReport::query();

        if (!$user->isAdmin()) {
            $query->where(function($q) use ($userRole) {
                $q->whereJsonContains('allowed_roles', $userRole)
                  ->orWhereNull('allowed_roles')
                  ->orWhere('allowed_roles', '[]');
            });
        }

        $reports = $query->get();

        return view('dynamic-reports.index', compact('reports'));
    }

    public function create()
    {
        return view('dynamic-reports.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'view_name' => 'required|string|max:255',
            'date_column' => 'nullable|string|max:255',
            'allowed_roles' => 'nullable|array',
            'show_grand_total' => 'nullable|boolean',
        ]);

        $data = $request->all();
        if(!isset($data['allowed_roles'])) {
            $data['allowed_roles'] = [];
        }
        $data['show_grand_total'] = $request->has('show_grand_total');

        DynamicReport::create($data);

        return redirect()->route('dynamic-reports.index')->with('success', 'Report configuration created successfully.');
    }

    public function edit(DynamicReport $dynamicReport)
    {
        return view('dynamic-reports.edit', compact('dynamicReport'));
    }

    public function update(Request $request, DynamicReport $dynamicReport)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'view_name' => 'required|string|max:255',
            'date_column' => 'nullable|string|max:255',
            'allowed_roles' => 'nullable|array',
            'show_grand_total' => 'nullable|boolean',
        ]);

        $data = $request->all();
        if(!isset($data['allowed_roles'])) {
            $data['allowed_roles'] = [];
        }
        $data['show_grand_total'] = $request->has('show_grand_total');

        $dynamicReport->update($data);

        return redirect()->route('dynamic-reports.index')->with('success', 'Report configuration updated successfully.');
    }

    public function destroy(DynamicReport $dynamicReport)
    {
        $dynamicReport->delete();
        return redirect()->route('dynamic-reports.index')->with('success', 'Report configuration deleted successfully.');
    }

    private function getReportData(DynamicReport $dynamicReport, Request $request)
    {
        $query = DB::table($dynamicReport->view_name);

        if ($dynamicReport->date_column) {
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween($dynamicReport->date_column, [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            } elseif ($request->filled('start_date')) {
                $query->where($dynamicReport->date_column, '>=', $request->start_date . ' 00:00:00');
            } elseif ($request->filled('end_date')) {
                $query->where($dynamicReport->date_column, '<=', $request->end_date . ' 23:59:59');
            }
        }

        $data = $query->get();
        $headings = $data->count() > 0 ? array_keys((array)$data->first()) : [];

        return [$data, $headings];
    }

    private function checkAccess(DynamicReport $dynamicReport)
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return;
        }

        $userRole = $user->role;
        $allowedRoles = $dynamicReport->allowed_roles ?? [];
        if (!empty($allowedRoles) && !in_array($userRole, $allowedRoles)) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function show(DynamicReport $dynamicReport, Request $request)
    {
        $this->checkAccess($dynamicReport);

        try {
            list($data, $headings) = $this->getReportData($dynamicReport, $request);
        } catch (\Exception $e) {
            return back()->with('error', 'Database View not found or query error: ' . $e->getMessage());
        }

        return view('dynamic-reports.show', compact('dynamicReport', 'data', 'headings'));
    }

    public function export(DynamicReport $dynamicReport, Request $request, $type)
    {
        $this->checkAccess($dynamicReport);

        try {
            list($data, $headings) = $this->getReportData($dynamicReport, $request);
        } catch (\Exception $e) {
            return back()->with('error', 'Database View not found or query error: ' . $e->getMessage());
        }

        $export = new DynamicDataExport($data, $headings);
        $fileName = Str::slug($dynamicReport->name) . '_' . date('Ymd_His');

        if ($type === 'excel') {
            return Excel::download($export, $fileName . '.xlsx');
        } elseif ($type === 'csv') {
            return Excel::download($export, $fileName . '.csv', \Maatwebsite\Excel\Excel::CSV);
        } elseif ($type === 'pdf') {
            $pdf = Pdf::loadView('dynamic-reports.pdf', compact('dynamicReport', 'data', 'headings'))
                ->setPaper('a4', 'landscape');
            return $pdf->download($fileName . '.pdf');
        }

        return back();
    }
}
