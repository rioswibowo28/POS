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

class TaxSalesDetailExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $orders;
    protected $summary;
    protected $taxPercentage;
    protected $startDate;
    protected $endDate;
    protected $rowCount = 0;

    public function __construct(Collection $orders, array $summary, string $taxPercentage, string $startDate, string $endDate)
    {
        $this->orders = $orders;
        $this->summary = $summary;
        $this->taxPercentage = $taxPercentage;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $this->rowCount = $this->orders->count();
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Jam',
            'No. Bill',
            'No. Order',
            'Pembayaran',
            'Item',
            'DPP (Subtotal)',
            'Diskon',
            'PPN (' . $this->taxPercentage . '%)',
            'Total',
        ];
    }

    public function map($order): array
    {
        static $no = 0;
        $no++;

        $items = $order->items->map(function ($item) {
            return $item->name . ' x' . $item->quantity . ' @Rp' . number_format($item->price, 0, ',', '.');
        })->join('; ');

        $payments = $order->payments->count() > 0 
            ? $order->payments->map(fn($p) => $p->method->label())->join(', ') 
            : '-';

        return [
            $no,
            $order->created_at->format('d/m/Y'),
            $order->created_at->format('H:i'),
            $order->bill_number,
            $order->order_number,
            $payments,
            $items,
            $order->subtotal,
            $order->discount,
            $order->tax_amount,
            $order->total,
        ];
    }

    public function title(): string
    {
        return 'Detail Penjualan';
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

                // Add total row
                $sheet->setCellValue('A' . $totalRow, '');
                $sheet->setCellValue('G' . $totalRow, 'TOTAL');
                $sheet->setCellValue('H' . $totalRow, $this->summary['subtotal']);
                $sheet->setCellValue('I' . $totalRow, $this->summary['discount']);
                $sheet->setCellValue('J' . $totalRow, $this->summary['total_tax']);
                $sheet->setCellValue('K' . $totalRow, $this->summary['total_sales']);

                // Format total row
                $sheet->getStyle('A' . $totalRow . ':K' . $totalRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E5E7EB']],
                ]);

                // Format currency columns
                $numberFormat = '#,##0';
                $sheet->getStyle('H2:H' . $totalRow)->getNumberFormat()->setFormatCode($numberFormat);
                $sheet->getStyle('I2:I' . $totalRow)->getNumberFormat()->setFormatCode($numberFormat);
                $sheet->getStyle('J2:J' . $totalRow)->getNumberFormat()->setFormatCode($numberFormat);
                $sheet->getStyle('K2:K' . $totalRow)->getNumberFormat()->setFormatCode($numberFormat);

                // Borders
                $sheet->getStyle('A1:K' . $totalRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                // Align right for currency columns
                $sheet->getStyle('H2:K' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
