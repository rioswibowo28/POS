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
        // Add to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('cashier_id')->constrained('users')->onDelete('set null');
            $table->foreignId('paid_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->after('paid_by')->constrained('users')->onDelete('set null');
        });

        // Add to temp_orders table
        Schema::table('temp_orders', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('cashier_id')->constrained('users')->onDelete('set null');
            $table->foreignId('paid_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->after('paid_by')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['paid_by']);
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['created_by', 'paid_by', 'deleted_by']);
        });

        Schema::table('temp_orders', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['paid_by']);
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['created_by', 'paid_by', 'deleted_by']);
        });
    }
};
