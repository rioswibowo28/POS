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
        Schema::table('dynamic_reports', function (Blueprint $table) {
            $table->string('date_column')->nullable()->after('view_name');
            $table->json('allowed_roles')->nullable()->after('date_column');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dynamic_reports', function (Blueprint $table) {
            $table->dropColumn(['date_column', 'allowed_roles']);
        });
    }
};
