<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add midtrans to payment method enum
        DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('cash', 'card', 'e_wallet', 'qris', 'bank_transfer', 'midtrans')");
        
        Schema::table('payments', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->after('reference_number');
            $table->string('payment_type')->nullable()->after('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['transaction_id', 'payment_type']);
        });
        
        DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('cash', 'card', 'e_wallet', 'qris', 'bank_transfer')");
    }
};
