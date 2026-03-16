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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, number, boolean, json
            $table->timestamps();
        });
        
        // Insert default settings
        DB::table('settings')->insert([
            ['key' => 'restaurant_name', 'value' => 'POS Resto', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'restaurant_logo', 'value' => null, 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'restaurant_address', 'value' => 'Jl. Contoh No. 123, Jakarta', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'restaurant_phone', 'value' => '021-12345678', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'restaurant_email', 'value' => 'info@posresto.com', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'tax_percentage', 'value' => '10', 'type' => 'number', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'service_charge', 'value' => '5', 'type' => 'number', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'receipt_footer', 'value' => 'Terima kasih atas kunjungan Anda!', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'currency', 'value' => 'IDR', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
