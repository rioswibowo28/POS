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
            $table->boolean('show_grand_total')->default(false)->after('allowed_roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dynamic_reports', function (Blueprint $table) {
            $table->dropColumn('show_grand_total');
        });
    }
};
