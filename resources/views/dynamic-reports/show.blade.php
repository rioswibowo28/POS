@extends('layouts.app')

@section('title', $dynamicReport->name)
@section('header', 'Report Preview: ' . $dynamicReport->name)

@section('content')
<div class="px-6 py-4">
    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <div class="mb-4">
        <a href="{{ route('dynamic-reports.index') }}" class="btn-secondary inilne-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to Report List
        </a>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center bg-gradient-to-r from-gray-50 to-white gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $dynamicReport->name }}</h2>
            @if($dynamicReport->description)
                <p class="text-gray-600 mt-1">{{ $dynamicReport->description }}</p>
            @endif
            <p class="text-sm font-mono text-gray-500 mt-2"><i class="fas fa-database mr-1"></i> Data Source: {{ $dynamicReport->view_name }}</p>
        </div>

        <div class="flex flex-col items-end gap-3">
            @php
                $showGrandTotal = request()->has('show_grand_total') ? request('show_grand_total') : $dynamicReport->show_grand_total;
            @endphp
            <form action="{{ route('dynamic-reports.show', $dynamicReport->id) }}" method="GET" class="flex gap-3 items-end flex-wrap">
                @if($dynamicReport->date_column)
                    <div>
                        <label class="text-xs text-gray-500">Start Date</label> 
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="input py-1 px-2 text-sm border-gray-300 rounded">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">End Date</label>   
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="input py-1 px-2 text-sm border-gray-300 rounded">
                    </div>
                @endif
                <div class="flex items-center mb-1">
                    <label class="flex items-center space-x-2 text-sm text-gray-700 bg-white border border-gray-300 px-3 py-1 rounded cursor-pointer hover:bg-gray-50 shadow-sm">
                        <input type="checkbox" name="show_grand_total" value="1" {{ $showGrandTotal ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-primary-600">
                        <span class="font-medium">Show Grand Total</span>
                    </label>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-primary-600 text-white px-3 py-1.5 rounded text-sm hover:bg-primary-700 shadow-sm transition-colors">Apply</button>
                    @if(request('start_date') || request('end_date') || request('show_grand_total') !== null)
                        <a href="{{ route('dynamic-reports.show', $dynamicReport->id) }}" class="bg-gray-200 text-gray-700 px-3 py-1.5 rounded text-sm hover:bg-gray-300 shadow-sm transition-colors">Clear</a>
                    @endif
                </div>
            </form>

            <div class="flex gap-2">
                @if(!session('error') && isset($data) && count($data) > 0)      
                    @php
                        $exportParams = request()->only(['start_date', 'end_date', 'show_grand_total']);
                    @endphp
                    <a href="{{ route('dynamic-reports.export', array_merge(['dynamic_report' => $dynamicReport->id, 'type' => 'excel'], $exportParams)) }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded text-sm font-semibold transition-colors flex items-center shadow-sm">
                        <i class="fas fa-file-excel mr-2"></i> Excel
                    </a>
                    <a href="{{ route('dynamic-reports.export', array_merge(['dynamic_report' => $dynamicReport->id, 'type' => 'csv'], $exportParams)) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-sm font-semibold transition-colors flex items-center shadow-sm">
                        <i class="fas fa-file-csv mr-2"></i> CSV
                    </a>
                    <a href="{{ route('dynamic-reports.export', array_merge(['dynamic_report' => $dynamicReport->id, 'type' => 'pdf'], $exportParams)) }}" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-sm font-semibold transition-colors flex items-center shadow-sm">
                        <i class="fas fa-file-pdf mr-2"></i> PDF
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if(!session('error'))
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-800 text-white">
                            @foreach($headings as $heading)
                                <th class="px-6 py-3 font-semibold uppercase tracking-wider text-xs border-b border-gray-700">
                                    {{ str_replace('_', ' ', $heading) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php $totals = []; @endphp
                        @forelse($data as $row)
                        <tr class="hover:bg-gray-50">
                            @foreach((array)$row as $col => $cell)
                                @php
                                    $isNumericCol = is_numeric($cell);
                                    // Do not sum or format IDs usually, but without explicit schema, we guess based on value or column name
                                    // Let's format anything numeric, but skip "Phone", "ID" strings if they have leading zeros (is_numeric treats "0812" as true though in PHP8 it might not, better check string)
                                    if(is_string($cell) && str_starts_with($cell, '0')) {
                                        $isNumericCol = false;
                                    }
                                    
                                    // Do not sum column names containing 'id', 'number', 'phone'
                                    $colLower = strtolower($col);
                                    $isIdOrPhone = str_contains($colLower, 'id') || str_contains($colLower, 'number') || str_contains($colLower, 'phone') || str_contains($colLower, 'no');
                                    
                                    if ($isIdOrPhone) {
                                        $isNumericCol = false; 
                                    }

                                    if ($showGrandTotal && $isNumericCol) {
                                        if (!isset($totals[$col])) $totals[$col] = 0;
                                        $totals[$col] += (float)$cell;
                                    }
                                @endphp
                                <td class="px-6 py-3 text-sm text-gray-800">
                                    {{ $isNumericCol ? number_format((float)$cell, 0, ',', '.') : $cell }}
                                </td>
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ count($headings) > 0 ? count($headings) : 1 }}" class="px-6 py-8 text-center text-gray-500">
                                No data available in this report.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($showGrandTotal && count($data) > 0 && !empty($totals))
                    <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-300">
                        <tr>
                            @foreach((array)$data->first() as $col => $cell)
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    @php
                                        $colLower = strtolower($col);
                                        $isIdOrPhone = str_contains($colLower, 'id') || str_contains($colLower, 'number') || str_contains($colLower, 'phone') || str_contains($colLower, 'no');
                                    @endphp
                                    {{ isset($totals[$col]) && !$isIdOrPhone ? number_format($totals[$col], 0, ',', '.') : ($loop->first ? 'GRAND TOTAL' : '') }}
                                </td>
                            @endforeach
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endif
</div>
@endsection