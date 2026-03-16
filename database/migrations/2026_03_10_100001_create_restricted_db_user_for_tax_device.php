<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Buat MySQL user khusus untuk aplikasi pihak ketiga (device perekaman pajak).
 * 
 * User ini HANYA bisa SELECT pada database views:
 * - v_orders_public (orders flag=0)
 * - v_order_items_public (items dari orders flag=0) 
 * - v_payments_public (payments dari orders flag=0)
 * 
 * User ini TIDAK bisa akses tabel orders, order_items, payments secara langsung.
 */
return new class extends Migration
{
    private string $dbUser = 'pos_tax_reader';
    private string $dbPass = 'TaxDevice2026!Secure';

    public function up(): void
    {
        $dbName = config('database.connections.mysql.database');

        // Drop user jika sudah ada
        DB::statement("DROP USER IF EXISTS '{$this->dbUser}'@'localhost'");
        DB::statement("DROP USER IF EXISTS '{$this->dbUser}'@'127.0.0.1'");

        // Buat user baru
        DB::statement("CREATE USER '{$this->dbUser}'@'localhost' IDENTIFIED BY '{$this->dbPass}'");
        DB::statement("CREATE USER '{$this->dbUser}'@'127.0.0.1' IDENTIFIED BY '{$this->dbPass}'");

        // Berikan HANYA akses SELECT pada views publik
        DB::statement("GRANT SELECT ON `{$dbName}`.`v_orders_public` TO '{$this->dbUser}'@'localhost'");
        DB::statement("GRANT SELECT ON `{$dbName}`.`v_order_items_public` TO '{$this->dbUser}'@'localhost'");
        DB::statement("GRANT SELECT ON `{$dbName}`.`v_payments_public` TO '{$this->dbUser}'@'localhost'");

        DB::statement("GRANT SELECT ON `{$dbName}`.`v_orders_public` TO '{$this->dbUser}'@'127.0.0.1'");
        DB::statement("GRANT SELECT ON `{$dbName}`.`v_order_items_public` TO '{$this->dbUser}'@'127.0.0.1'");
        DB::statement("GRANT SELECT ON `{$dbName}`.`v_payments_public` TO '{$this->dbUser}'@'127.0.0.1'");

        // Flush privileges
        DB::statement("FLUSH PRIVILEGES");
    }

    public function down(): void
    {
        DB::statement("DROP USER IF EXISTS '{$this->dbUser}'@'localhost'");
        DB::statement("DROP USER IF EXISTS '{$this->dbUser}'@'127.0.0.1'");
        DB::statement("FLUSH PRIVILEGES");
    }
};
