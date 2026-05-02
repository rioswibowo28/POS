<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("\n            CREATE OR REPLACE VIEW v_orders_public AS\n            SELECT \n                id, order_number, bill_number, table_id,\n                customer_name, customer_phone, type, status,\n                subtotal, tax, tax_amount, discount, total,\n                notes,\n                DATE(business_date) AS business_date,\n                completed_at,\n                created_at, updated_at\n            FROM orders\n            WHERE flag = 0\n              AND deleted_at IS NULL\n            ORDER BY DATE(business_date) ASC, bill_number ASC\n        ");
    }

    public function down(): void
    {
        DB::statement("\n            CREATE OR REPLACE VIEW v_orders_public AS\n            SELECT \n                id, order_number, bill_number, table_id, \n                customer_name, customer_phone, type, status,\n                subtotal, tax, tax_amount, discount, total,\n                notes, completed_at,\n                created_at, updated_at\n            FROM orders\n            WHERE flag = 0\n              AND deleted_at IS NULL\n        ");
    }
};