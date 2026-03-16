<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Collection;

class InternalRevenueExport implements WithMultipleSheets
{
    use Exportable;

    protected $allOrders;
    protected $normalOrders;
    protected $tempOrders;
    protected $summary;
    protected $startDate;
    protected $endDate;

    public function __construct(Collection $allOrders, Collection $normalOrders, Collection $tempOrders, array $summary, string $startDate, string $endDate)
    {
        $this->allOrders = $allOrders;
        $this->normalOrders = $normalOrders;
        $this->tempOrders = $tempOrders;
        $this->summary = $summary;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function sheets(): array
    {
        return [
            new InternalRevenueRecapSheet($this->summary, $this->startDate, $this->endDate),
            new InternalRevenueOrderSheet('All Transaction', $this->allOrders, 'all'),
            new InternalRevenueOrderSheet('Normal', $this->normalOrders, 'normal'),
            new InternalRevenueOrderSheet('Other Transaction', $this->tempOrders, 'temp'),
        ];
    }
}
