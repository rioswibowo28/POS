<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class TaxSalesRecapExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $dailyRecap;
    protected $summary;
    protected $taxPercentage;
    protected $startDate;
    protected $endDate;
    protected $rowCount = 0;

    public function __construct(Collection $dailyRecap, array $summary, string $taxPercentage, string $startDate, string $endDate)
    {
        $this->dailyRecap = $dailyRecap;
        $this->summary = $summary;
        $this->taxPercentage = $taxPercentage;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $this->rowCount = $this->dailyRecap->count();
        return $this->dailyRecap;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Jumlah Transaksi',
            'DPP (Subtotal)',
            'Diskon',
            'PPN (' . $this->taxPercentage . '%)',
            'Total',
        ];
    }

    public function map($day): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            Carbon::parse($day['date'])->translatedFormat('l, d F Y'),
            $day['total_orders'],
            $day['subtotal'],
            $day['discount'],
            $day['tax_amount'],
            $day['total'],
        ];
    }

    public function title(): string
    {
        return 'Rekap Harian';
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
                $lastRow = $this->rowCount + 1;
                $totalRow = $lastRow + 1;

                // Total row
                $sheet->setCellValue('A' . $totalRow, '');
                $sheet->setCellValue('B' . $totalRow, 'TOTAL');
                $sheet->setCellValue('C' . $totalRow, $this->summary['total_orders']);
                $sheet->setCellValue('D' . $totalRow, $this->summary['subtotal']);
                $sheet->setCellValue('E' . $totalRow, $this->summary['discount']);
                $sheet->setCellValue('F' . $totalRow, $this->summary['total_tax']);
                $sheet->setCellValue('G' . $totalRow, $this->summary['total_sales']);

                $sheet->getStyle('A' . $totalRow . ':G' . $totalRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E5E7EB']],
                ]);

                // Number format
                $numberFormat = '#,##0';
                $sheet->getStyle('D2:D' . $totalRow)->getNumberFormat()->setFormatCode($numberFormat);
                $sheet->getStyle('E2:E' . $totalRow)->getNumberFormat()->setFormatCode($numberFormat);
                $sheet->getStyle('F2:F' . $totalRow)->getNumberFormat()->setFormatCode($numberFormat);
                $sheet->getStyle('G2:G' . $totalRow)->getNumberFormat()->setFormatCode($numberFormat);

                // Center transaction count
                $sheet->getStyle('C2:C' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Right align currency
                $sheet->getStyle('D2:G' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Borders
                $sheet->getStyle('A1:G' . $totalRow)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
            },
        ];
    }
}
