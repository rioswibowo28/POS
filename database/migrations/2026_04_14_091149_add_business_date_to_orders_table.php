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
        Schema::table('orders', function (Blueprint $table) {
            $table->date('business_date')->nullable()->after('created_at');
        });

        // Setup default settings for late night trading
        \DB::table('settings')->insertOrIgnore([
            ['key' => 'enable_late_night_trading', 'value' => '0'],
            ['key' => 'late_night_start_time', 'value' => '00:00'],
            ['key' => 'late_night_end_time', 'value' => '03:00'],
        ]);

        // Skenario B: Update data lama
        \DB::table('orders')
            ->whereTime('created_at', '>=', '03:00:00')
            ->update([
                'business_date' => \DB::raw('DATE(created_at)')
            ]);

        \DB::table('orders')
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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('business_date');
        });

        \DB::table('settings')->whereIn('key', [
            'enable_late_night_trading',
            'late_night_start_time',
            'late_night_end_time'
        ])->delete();
    }
};
