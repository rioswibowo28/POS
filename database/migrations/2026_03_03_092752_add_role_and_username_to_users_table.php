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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('id');
            $table->enum('role', ['admin', 'cashier'])->default('cashier')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
        });
        
        // Update existing users with username from their names
        DB::statement("UPDATE users SET username = LOWER(REPLACE(name, ' ', '_')) WHERE username IS NULL");
        
        // Make username unique after populating
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'role', 'is_active']);
        });
    }
};
