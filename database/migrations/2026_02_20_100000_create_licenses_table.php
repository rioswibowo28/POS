<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('license_key')->unique();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('business_name')->nullable();
            $table->enum('status', ['active', 'expired', 'suspended', 'trial'])->default('active');
            $table->enum('license_type', ['trial', 'monthly', 'yearly', 'lifetime'])->default('yearly');
            $table->date('start_date');
            $table->date('expiry_date')->nullable();
            $table->integer('max_users')->nullable();
            $table->integer('max_tables')->nullable();
            $table->string('activated_by_ip')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
