<?php

namespace App\Console\Commands;

use App\Models\Table;
use App\Models\Order;
use App\Enums\TableStatus;
use Illuminate\Console\Command;

class SyncTableStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tables:sync-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync table status berdasarkan active orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing table status...');
        
        $tables = Table::all();
        $updated = 0;
        
        foreach ($tables as $table) {
            // Check apakah ada order aktif (pending atau processing) untuk table ini
            $hasActiveOrder = Order::where('table_id', $table->id)
                ->whereIn('status', ['pending', 'processing'])
                ->exists();
            
            $newStatus = $hasActiveOrder ? TableStatus::OCCUPIED : TableStatus::AVAILABLE;
            
            if ($table->status !== $newStatus) {
                $table->status = $newStatus;
                $table->save();
                $updated++;
                
                $this->line("Table {$table->number}: {$table->status->value} -> {$newStatus->value}");
            }
        }
        
        $this->info("Sync complete! {$updated} table(s) updated.");
        
        return Command::SUCCESS;
    }
}
