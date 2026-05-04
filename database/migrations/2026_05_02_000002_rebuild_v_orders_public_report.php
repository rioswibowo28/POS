<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("\n            CREATE OR REPLACE VIEW v_orders_public AS\n            SELECT\n                orders.id,\n                orders.order_number,\n                orders.bill_number,\n                orders.table_id,\n                orders.customer_name,\n                orders.customer_phone,\n                orders.type,\n                orders.status,\n                orders.subtotal,\n                orders.tax,\n                orders.tax_amount,\n                orders.discount,\n                orders.total,\n                orders.notes,\n                orders.business_date,\n                orders.completed_at,\n                orders.created_at,\n                orders.updated_at\n            FROM orders\n            WHERE orders.flag = 0\n              AND orders.deleted_at IS NULL\n              AND orders.status = 'completed'\n            ORDER BY orders.business_date ASC, orders.bill_number ASC\n        ");
    }

    public function down(): void
    {
        DB::statement("\n            CREATE OR REPLACE VIEW v_orders_public AS\n            SELECT \n                id, order_number, bill_number, table_id, \n                customer_name, customer_phone, type, status,\n                subtotal, tax, tax_amount, discount, total,\n                notes, completed_at,\n                created_at, updated_at\n            FROM orders\n            WHERE flag = 0\n              AND deleted_at IS NULL\n        ");
    }
};