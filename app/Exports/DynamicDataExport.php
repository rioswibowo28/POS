<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class DynamicDataExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    protected $headings;

    public function __construct($data, $headings)
    {
        $this->data = collect($data);
        $this->headings = $headings;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        $mappedRow = [];
        foreach ($this->headings as $heading) {
            $value = is_array($row) ? ($row[$heading] ?? null) : ($row->$heading ?? null);
            
            if ($heading === 'completed_at' && $value) {
                try {
                    $value = Carbon::parse($value)->format('n/j/Y');
                } catch (\Exception $e) {
                    // Do nothing
                }
            }
            $mappedRow[] = $value;
        }
        return $mappedRow;
    }
}

