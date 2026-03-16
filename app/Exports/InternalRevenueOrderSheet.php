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

class InternalRevenueOrderSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $sheetTitle;
    protected $orders;
    protected $type;
    protected $rowCount = 0;

    public function __construct(string $sheetTitle, Collection $orders, string $type)
    {
        $this->sheetTitle = $sheetTitle;
        $this->orders = $orders;
        $this->type = $type;
    }

    public function collection()
    {
        $this->rowCount = $this->orders->count();
        return $this->orders;
    }

    public function headings(): array
    {
        $headings = [
            'No',
            'Tanggal',
            'Jam',
            'No. Bill',
            'No. Order',
            'Pembayaran',
        ];

        if ($this->type === 'all') {
            $headings[] = 'Sumber';
        }

        return array_merge($headings, [
            'Subtotal',
            'PPN',
            'Diskon',
            'Total',
        ]);
    }

    public function map($order): array
    {
        static $counters = [];
        if (!isset($counters[$this->type])) {
            $counters[$this->type] = 0;
        }
        $counters[$this->type]++;

        // Determine payment method
        $source = $order->_source ?? '';
        if ($source === 'order' && $order->payments && $order->payments->count() > 0) {
            $paymentLabel = $order->payments->map(fn($p) => $p->method->label())->join(', ');
        } elseif ($source === 'temp' && $order->payment_method) {
            $paymentLabel = ucfirst($order->payment_method);
        } elseif (!$source && $order->payments && $order->payments->count() > 0) {
            // Normal tab (only Order models)
            $paymentLabel = $order->payments->map(fn($p) => $p->method->label())->join(', ');
        } elseif (!$source && property_exists($order, 'payment_method') && $order->payment_method) {
            // Temp tab (only TempOrder models)
            $paymentLabel = ucfirst($order->payment_method);
        } else {
            $paymentLabel = '-';
        }

        $row = [
            $counters[$this->type],
            $order->created_at->format('d/m/Y'),
            $order->created_at->format('H:i'),
            $order->bill_number ?? '-',
            $order->order_number ?? '-',
            $paymentLabel,
        ];

        if ($this->type === 'all') {
            $row[] = ($order->_source ?? '') === 'temp' ? 'Temp' : 'Order';
        }

        return array_merge($row, [
            $order->subtotal,
            $order->tax_amount,
            $order->discount,
            $order->total,
        ]);
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function styles(Worksheet $sheet): array
    {
        $colors = [
            'all' => '16A34A',
            'normal' => '3B82F6',
            'temp' => '8B5CF6',
        ];

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colors[$this->type] ?? '0284C7']],
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

                // Column references differ based on type (all has extra Sumber column)
                $lastCol = $this->type === 'all' ? 'K' : 'J';
                $totalLabelCol = $this->type === 'all' ? 'G' : 'F';
                $subtotalCol = $this->type === 'all' ? 'H' : 'G';
                $taxCol = $this->type === 'all' ? 'I' : 'H';
                $discountCol = $this->type === 'all' ? 'J' : 'I';
                $grandTotalCol = $this->type === 'all' ? 'K' : 'J';

                if ($this->rowCount > 0) {
                    // Total row
                    $sheet->setCellValue($totalLabelCol . $totalRow, 'TOTAL');
                    $sheet->setCellValue($subtotalCol . $totalRow, '=SUM(' . $subtotalCol . '2:' . $subtotalCol . $lastRow . ')');
                    $sheet->setCellValue($taxCol . $totalRow, '=SUM(' . $taxCol . '2:' . $taxCol . $lastRow . ')');
                    $sheet->setCellValue($discountCol . $totalRow, '=SUM(' . $discountCol . '2:' . $discountCol . $lastRow . ')');
                    $sheet->setCellValue($grandTotalCol . $totalRow, '=SUM(' . $grandTotalCol . '2:' . $grandTotalCol . $lastRow . ')');

                    $sheet->getStyle('A' . $totalRow . ':' . $lastCol . $totalRow)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E5E7EB']],
                    ]);

                    // Number format
                    $numberFormat = '#,##0';
                    $sheet->getStyle($subtotalCol . '2:' . $subtotalCol . $totalRow)->getNumberFormat()->setFormatCode($numberFormat);
                    $sheet->getStyle($taxCol . '2:' . $taxCol . $totalRow)->getNumberFormat()->setFormatCode($numberFormat);
                    $sheet->getStyle($discountCol . '2:' . $discountCol . $totalRow)->getNumberFormat()->setFormatCode($numberFormat);
                    $sheet->getStyle($grandTotalCol . '2:' . $grandTotalCol . $totalRow)->getNumberFormat()->setFormatCode($numberFormat);

                    // Right align currency
                    $sheet->getStyle($subtotalCol . '2:' . $lastCol . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // Borders
                    $sheet->getStyle('A1:' . $lastCol . $totalRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                }
            },
        ];
    }
}
