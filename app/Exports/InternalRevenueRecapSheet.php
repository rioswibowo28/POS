<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InternalRevenueRecapSheet implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $summary;
    protected $startDate;
    protected $endDate;

    public function __construct(array $summary, string $startDate, string $endDate)
    {
        $this->summary = $summary;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function array(): array
    {
        return [
            ['Normal (Order)', $this->summary['normal_count'], $this->summary['normal_subtotal'], $this->summary['normal_tax'], $this->summary['normal_discount'], $this->summary['normal_total']],
            ['Other Transaction (Temp)', $this->summary['temp_count'], $this->summary['temp_subtotal'], $this->summary['temp_tax'], $this->summary['temp_discount'], $this->summary['temp_total']],
            ['GRAND TOTAL', $this->summary['all_count'], $this->summary['all_subtotal'], $this->summary['all_tax'], $this->summary['all_discount'], $this->summary['all_total']],
        ];
    }

    public function headings(): array
    {
        return [
            'Kategori',
            'Jumlah Order',
            'Subtotal',
            'PPN',
            'Diskon',
            'Total',
        ];
    }

    public function title(): string
    {
        return 'Rekap Penjualan';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0284C7']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Grand total row styling (row 4 = heading + 3 data rows)
                $sheet->getStyle('A4:F4')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                ]);

                // Number format
                $numberFormat = '#,##0';
                $sheet->getStyle('C2:C4')->getNumberFormat()->setFormatCode($numberFormat);
                $sheet->getStyle('D2:D4')->getNumberFormat()->setFormatCode($numberFormat);
                $sheet->getStyle('E2:E4')->getNumberFormat()->setFormatCode($numberFormat);
                $sheet->getStyle('F2:F4')->getNumberFormat()->setFormatCode($numberFormat);

                // Center count column
                $sheet->getStyle('B2:B4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Right align currency
                $sheet->getStyle('C2:F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Borders
                $sheet->getStyle('A1:F4')->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // Row colors
                $sheet->getStyle('A2:F2')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                ]);
                $sheet->getStyle('A3:F3')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9D5FF']],
                ]);
            },
        ];
    }
}
