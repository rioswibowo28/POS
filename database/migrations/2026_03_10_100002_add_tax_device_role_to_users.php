<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tambahkan role 'tax_device' ke enum kolom role di tabel users.
 * Role ini untuk akses API read-only oleh device perekaman pajak pihak ketiga.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'cashier', 'tax_device') NOT NULL DEFAULT 'cashier'");
    }

    public function down(): void
    {
        // Hapus user tax_device dulu sebelum rollback enum
        DB::table('users')->where('role', 'tax_device')->delete();
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'cashier') NOT NULL DEFAULT 'cashier'");
    }
};
