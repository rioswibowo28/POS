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
        Schema::table('temp_orders', function (Blueprint $table) {
            $table->date('business_date')->nullable()->after('created_at');
        });

        // Update data lama
        \DB::table('temp_orders')
            ->whereTime('created_at', '>=', '03:00:00')
            ->update([
                'business_date' => \DB::raw('DATE(created_at)')
            ]);

        \DB::table('temp_orders')
            ->whereTime('created_at', '<', '03:00:00')
            ->update([
                'business_date' => \DB::raw('DATE_SUB(DATE(created_at), INTERVAL 1 DAY)')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('temp_orders', function (Blueprint $table) {
            $table->dropColumn('business_date');
        });
    }
};
