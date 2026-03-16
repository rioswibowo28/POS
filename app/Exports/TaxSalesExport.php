<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Collection;

class TaxSalesExport implements WithMultipleSheets
{
    use Exportable;

    protected $orders;
    protected $dailyRecap;
    protected $summary;
    protected $taxPercentage;
    protected $startDate;
    protected $endDate;

    public function __construct(Collection $orders, Collection $dailyRecap, array $summary, string $taxPercentage, string $startDate, string $endDate)
    {
        $this->orders = $orders;
        $this->dailyRecap = $dailyRecap;
        $this->summary = $summary;
        $this->taxPercentage = $taxPercentage;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function sheets(): array
    {
        return [
            new TaxSalesDetailExport($this->orders, $this->summary, $this->taxPercentage, $this->startDate, $this->endDate),
            new TaxSalesRecapExport($this->dailyRecap, $this->summary, $this->taxPercentage, $this->startDate, $this->endDate),
        ];
    }
}
