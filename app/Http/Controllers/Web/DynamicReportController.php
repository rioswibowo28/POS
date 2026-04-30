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

        $sortColumn = null;
        foreach (['bill_number', 'receipt_number', 'order_number'] as $col) {
            if (in_array($col, $headings)) {
                $sortColumn = $col;
                break;
            }
        }

        if ($sortColumn) {
            $data = collect($data)->sortBy(function($item) use ($sortColumn) {
                return is_array($item) ? $item[$sortColumn] : $item->{$sortColumn};
            }, SORT_NATURAL)->values();
        } elseif ($dynamicReport->date_column && in_array($dynamicReport->date_column, $headings)) {
            $data = collect($data)->sortBy(function($item) use ($dynamicReport) {
                return is_array($item) ? $item[$dynamicReport->date_column] : $item->{$dynamicReport->date_column};
            })->values();
        }

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

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        if ($startDate && $endDate) {
            if ($startDate === $endDate) {
                try {
                    $formattedDate = \Carbon\Carbon::parse($startDate)->format('Y-m-d');
                    $fileName = $formattedDate;
                } catch (\Exception $e) {
                    $fileName = $startDate;
                }
            } else {
                $fileName = Str::slug($dynamicReport->name) . '_' . $startDate . '_to_' . $endDate;
            }
        } elseif ($startDate) {
            $fileName = Str::slug($dynamicReport->name) . '_from_' . $startDate;
        } elseif ($endDate) {
            $fileName = Str::slug($dynamicReport->name) . '_until_' . $endDate;
        } else {
            $fileName = Str::slug($dynamicReport->name) . '_' . date('Ymd_His');
        }

        if ($type === 'excel' && $request->has('separate_files') && $request->separate_files == 1 && $dynamicReport->date_column) {
            $zip = new \ZipArchive();
            $zipFileName = 'export_' . Str::slug($dynamicReport->name) . '_' . date('YmdHis') . '.zip';
            $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipFileName;

            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                // Group data by date
                $groupedData = collect($data)->groupBy(function ($item) use ($dynamicReport) {
                    $dateStr = is_array($item) ? ($item[$dynamicReport->date_column] ?? null) : ($item->{$dynamicReport->date_column} ?? null);
                    if ($dateStr) {
                        try {
                            return \Carbon\Carbon::parse($dateStr)->format('Y-m-d');
                        } catch (\Exception $e) {
                            return 'Unknown_Date';
                        }
                    }
                    return 'Unknown_Date';
                });

                $filesToDelete = [];
                foreach ($groupedData as $date => $items) {
                    $export = new DynamicDataExport($items->toArray(), $headings);
                    $excelFile = 'temp_' . Str::slug($dynamicReport->name) . '_' . date('YmdHis') . '_' . $date . '.xlsx';
                    
                    // Store locally inside real OS temp directory directly bypassing Laravel disks
                    $tempExcelPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $excelFile;
                    Excel::store($export, $tempExcelPath, config('excel.temporary_files.local_disk', 'local'));
                    
                    // Laravel local disk means storage_path('app/' . $tempExcelPath) wait NO
                    // If we pass an absolute path using store, it doesn't work well.
                    // Better to use Excel::store with 'local' disk and relative path
                    Excel::store($export, 'temp_exports/' . $excelFile, 'local');
                    $realPath = storage_path('app/temp_exports/' . $excelFile);
                    
                    if (file_exists($realPath)) {
                        $zip->addFile($realPath, $date . '.xlsx');
                        $filesToDelete[] = $realPath;
                    }
                }
                $zip->close();
                
                foreach ($filesToDelete as $file) {
                    @unlink($file);
                }
                
                return response()->download($zipPath)->deleteFileAfterSend(true);
            }
        }

        $export = new DynamicDataExport($data, $headings);

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
