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
            $table->string('payment_method')->nullable()->after('completed_at');
            $table->decimal('payment_amount', 10, 2)->default(0)->after('payment_method');
            $table->decimal('payment_received', 10, 2)->default(0)->after('payment_amount');
            $table->decimal('payment_change', 10, 2)->default(0)->after('payment_received');
            $table->string('payment_reference')->nullable()->after('payment_change');
            $table->timestamp('payment_at')->nullable()->after('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('temp_orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_amount',
                'payment_received',
                'payment_change',
                'payment_reference',
                'payment_at',
            ]);
        });
    }
};
