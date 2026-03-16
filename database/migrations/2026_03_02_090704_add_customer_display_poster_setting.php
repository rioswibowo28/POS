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
        // Add customer display poster setting
        DB::table('settings')->insert([
            [
                'key' => 'customer_display_poster',
                'value' => null,
                'type' => 'string',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove customer display poster setting
        DB::table('settings')->where('key', 'customer_display_poster')->delete();
    }
};
