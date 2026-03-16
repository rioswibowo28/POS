<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing 'takeaway' values to 'take_away'
        DB::statement("UPDATE orders SET type = 'take_away' WHERE type = 'takeaway'");
        
        // Alter the enum to include 'take_away' instead of 'takeaway'
        DB::statement("ALTER TABLE orders MODIFY COLUMN type ENUM('dine_in', 'take_away', 'delivery') NOT NULL DEFAULT 'dine_in'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to 'takeaway'
        DB::statement("UPDATE orders SET type = 'takeaway' WHERE type = 'take_away'");
        
        // Alter the enum back to 'takeaway'
        DB::statement("ALTER TABLE orders MODIFY COLUMN type ENUM('dine_in', 'takeaway', 'delivery') NOT NULL DEFAULT 'dine_in'");
    }
};
