<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\TempOrder;
use App\Services\OrderNumberAuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixOrderNumberSequence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:fix-sequence {--dry-run : Preview changes without executing} {--business-date= : Fix only specific business date (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix and reorder bill numbers to be sequential, eliminating gaps';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $businessDate = $this->option('business-date');

        $this->info('========================================');
        $this->info('Order Number Sequence Fixer');
        $this->info('========================================');
        $this->newLine();

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        try {
            DB::beginTransaction();

            // Get all distinct (business_date, flag) combinations
            $groups = Order::selectRaw('business_date, flag')
                ->distinct()
                ->orderBy('business_date', 'desc')
                ->orderBy('flag')
                ->get();

            // Also include TempOrder groups
            $tempGroups = TempOrder::selectRaw('business_date, flag')
                ->distinct()
                ->orderBy('business_date', 'desc')
                ->orderBy('flag')
                ->get();

            $allGroups = $groups->merge($tempGroups)->unique(function ($item) {
                return $item->business_date . '-' . $item->flag;
            });

            if ($businessDate) {
                $allGroups = $allGroups->filter(function ($item) use ($businessDate) {
                    return $item->business_date->format('Y-m-d') === $businessDate;
                });
            }

            $totalFixed = 0;
            $totalChanges = 0;

            foreach ($allGroups as $group) {
                $fixed = $this->fixGroupSequence(
                    $group->business_date,
                    $group->flag,
                    $isDryRun
                );
                $totalFixed += $fixed['count'];
                $totalChanges += $fixed['changes'];
            }

            if (!$isDryRun) {
                DB::commit();
                $this->info("\n✅ Changes committed to database");
            } else {
                DB::rollBack();
                $this->warn("\n⏭️  DRY RUN: No changes were made");
            }

            $this->newLine();
            $this->info("📊 Summary:");
            $this->line("  • Transactions processed: $totalFixed");
            $this->line("  • Numbers renumbered: $totalChanges");
            $this->newLine();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Fix sequence for a specific group (business_date, flag)
     */
    private function fixGroupSequence($businessDate, $flag, $isDryRun = false)
    {
        // Get all orders for this date/flag combination, sorted by creation
        $orders = Order::where('business_date', $businessDate)
            ->where('flag', $flag)
            ->orderBy('created_at', 'asc')
            ->get();

        // Get all temp orders for this date/flag combination
        $tempOrders = TempOrder::where('business_date', $businessDate)
            ->where('flag', $flag)
            ->orderBy('created_at', 'asc')
            ->get();

        // Combine and sort by created_at to maintain creation order
        $allOrders = collect($orders)->merge($tempOrders)
            ->sortBy('created_at')
            ->values();

        if ($allOrders->isEmpty()) {
            return ['count' => 0, 'changes' => 0];
        }

        $flagLabel = $flag ? 'flag=1 (No-Tax)' : 'flag=0 (Normal)';
        
        $this->line("\n📅 Date: {$businessDate->format('d/m/Y')} | Type: {$flagLabel}");
        $this->line("   Processing " . $allOrders->count() . " transaction(s)...");

        $changeCount = 0;
        $changes = [];

        // Generate new sequential numbers
        foreach ($allOrders as $index => $order) {
            $sequence = $index + 1;
            
            // Determine padding: 4 digits for normal, 3 digits for flag=1
            $padding = $flag ? 3 : 4;
            $numStr = str_pad($sequence, $padding, '0', STR_PAD_LEFT);

            // Generate new numbers using correct date format (ymd = 6-digit)
            $correctDateStr = $businessDate->format('ymd');
            $newOrderNumber = 'ORD-' . $correctDateStr . '-' . $numStr;
            $newBillNumber = 'BILL-' . $correctDateStr . '-' . $numStr;

            // Check if this needs to be updated
            $isOrder = $order instanceof Order;
            $table = $isOrder ? 'orders' : 'temp_orders';
            $oldOrderNumber = $order->order_number;
            $oldBillNumber = $order->bill_number;

            if ($oldOrderNumber !== $newOrderNumber || $oldBillNumber !== $newBillNumber) {
                $changes[] = [
                    'id' => $order->id,
                    'table' => $table,
                    'old_order_number' => $oldOrderNumber,
                    'new_order_number' => $newOrderNumber,
                    'old_bill_number' => $oldBillNumber,
                    'new_bill_number' => $newBillNumber,
                ];
                $changeCount++;
            }
        }

        // Display changes
        if ($changeCount > 0) {
            if ($changeCount <= 10) {
                foreach ($changes as $change) {
                    $this->line("   {$change['table']} #{$change['id']}: {$change['old_bill_number']} → {$change['new_bill_number']}");
                }
            } else {
                $this->line("   Showing first 10 of $changeCount changes:");
                foreach (array_slice($changes, 0, 10) as $change) {
                    $this->line("   {$change['table']} #{$change['id']}: {$change['old_bill_number']} → {$change['new_bill_number']}");
                }
                $this->line("   ... and " . ($changeCount - 10) . " more");
            }

            // Apply changes if not dry run
            if (!$isDryRun) {
                foreach ($changes as $change) {
                    if ($change['table'] === 'orders') {
                        Order::where('id', $change['id'])->update([
                            'order_number' => $change['new_order_number'],
                            'bill_number' => $change['new_bill_number'],
                        ]);
                    } else {
                        TempOrder::where('id', $change['id'])->update([
                            'order_number' => $change['new_order_number'],
                            'bill_number' => $change['new_bill_number'],
                        ]);
                    }
                }
            }

            $this->line("   <fg=green>✓ {$changeCount} number(s) updated</>");
        } else {
            $this->line("   <fg=yellow>⚠ Already in sequence, no changes needed</>");
        }

        return [
            'count' => $allOrders->count(),
            'changes' => $changeCount,
        ];
    }
}
