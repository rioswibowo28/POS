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
        Schema::create('order_number_audits', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 50);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('action', 30);
            $table->string('order_number')->nullable()->index();
            $table->string('bill_number')->nullable()->index();
            $table->string('previous_order_number')->nullable();
            $table->string('previous_bill_number')->nullable();
            $table->date('business_date')->nullable()->index();
            $table->boolean('flag')->nullable()->index();
            $table->string('status', 50)->nullable();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_number_audits');
    }
};
