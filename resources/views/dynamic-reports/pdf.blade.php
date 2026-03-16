<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $dynamicReport->name }}</title>
    <style>
        @page { 
            margin: 15px 20px; 
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 7.5pt; 
            color: #111; 
            margin: 0; 
            padding: 0; 
        }
        .header { 
            text-align: center; 
            margin-bottom: 15px; 
            border-bottom: 1px solid #333; 
            padding-bottom: 10px; 
        }
        .title { 
            font-size: 12pt; 
            font-weight: bold; 
            margin: 0; 
            text-transform: uppercase; 
        }
        .subtitle { 
            font-size: 8pt; 
            color: #555; 
            margin-top: 4px; 
            margin-bottom: 0px; 
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 5px; 
            page-break-inside: auto; 
            table-layout: auto;
        }
        tr { 
            page-break-inside: avoid; 
            page-break-after: auto; 
        }
        th { 
            background-color: #e5e7eb; 
            color: #111; 
            font-weight: bold; 
            text-align: left; 
            padding: 4px 4px; 
            border: 1px solid #999; 
            word-wrap: break-word; 
            font-size: 7pt; 
        }
        td { 
            padding: 4px 4px; 
            border: 1px solid #ccc; 
            color: #222; 
            word-wrap: break-word; 
        }
        tr:nth-child(even) td { 
            background-color: #f9fafb; 
        }
        tfoot td { 
            background-color: #e5e7eb; 
            font-weight: bold; 
            border: 1px solid #999; 
        }
        
        .footer { 
            text-align: right; 
            margin-top: 15px; 
            font-size: 7pt; 
            color: #888; 
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    @php
        $showGrandTotal = request()->has('show_grand_total') ? request('show_grand_total') : $dynamicReport->show_grand_total;
    @endphp

    <div class="header">
        <h1 class="title">{{ $dynamicReport->name }}</h1>
        @if($dynamicReport->description)
            <p class="subtitle">{{ $dynamicReport->description }}</p>
        @endif
        <p class="subtitle">Generated on: {{ now()->format('d M Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                @foreach($headings as $heading)
                    @php
                        $hLower = strtolower($heading);
                        $isNumericLike = !str_contains($hLower, 'id') && !str_contains($hLower, 'number') && !str_contains($hLower, 'phone') && !str_contains($hLower, 'no') && !str_contains($hLower, 'name') && !str_contains($hLower, 'status') && !str_contains($hLower, 'type') && !str_contains($hLower, 'date') && !str_contains($hLower, 'at') && !str_contains($hLower, 'notes');
                    @endphp
                    <th class="{{ $isNumericLike ? 'text-right' : '' }}">{{ ucwords(str_replace('_', ' ', $heading)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php $totals = []; @endphp
            @forelse($data as $row)
            <tr>
                @foreach((array)$row as $col => $cell)
                    @php
                        $isNumericCol = is_numeric($cell);
                        if(is_string($cell) && str_starts_with($cell, '0')) {
                            $isNumericCol = false;
                        }
                        
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
                    <td class="{{ $isNumericCol ? 'text-right' : '' }}">
                        {{ $isNumericCol ? number_format((float)$cell, 0, ',', '.') : $cell }}
                    </td>
                @endforeach
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($headings) > 0 ? count($headings) : 1 }}" class="text-center" style="padding: 20px;">
                    No data available in this report.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($showGrandTotal && count($data) > 0 && !empty($totals))
        <tfoot>
            <tr>
                @foreach((array)$data->first() as $col => $cell)
                    @php
                        $colLower = strtolower($col);
                        $isIdOrPhone = str_contains($colLower, 'id') || str_contains($colLower, 'number') || str_contains($colLower, 'phone') || str_contains($colLower, 'no');
                        $isNumericCol = isset($totals[$col]) && !$isIdOrPhone;
                    @endphp
                    <td class="{{ $isNumericCol ? 'text-right' : '' }}">
                        {{ $isNumericCol ? number_format($totals[$col], 0, ',', '.') : ($loop->first ? 'GRAND TOTAL' : '') }}
                    </td>
                @endforeach
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Generated by {{ \App\Models\Setting::get('restaurant_name', 'POS System') }}
    </div>
</body>
</html>