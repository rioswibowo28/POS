<?php

namespace App\Services;

use App\Models\OrderNumberAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class OrderNumberAuditService
{
    public function record(Model $record, string $action, array $context = []): ?OrderNumberAudit
    {
        try {
            return OrderNumberAudit::create([
                'source_type' => class_basename($record),
                'source_id' => $record->getKey(),
                'action' => $action,
                'order_number' => $record->order_number ?? null,
                'bill_number' => $record->bill_number ?? null,
                'previous_order_number' => $context['previous_order_number'] ?? null,
                'previous_bill_number' => $context['previous_bill_number'] ?? null,
                'business_date' => $record->business_date ?? null,
                'flag' => $record->flag ?? null,
                'status' => isset($record->status) && is_object($record->status) ? ($record->status->value ?? (string) $record->status) : ($record->status ?? null),
                'shift_id' => $record->shift_id ?? null,
                'user_id' => auth()->id(),
                'context' => $context,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to record order number audit', [
                'source_type' => class_basename($record),
                'source_id' => $record->getKey(),
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
