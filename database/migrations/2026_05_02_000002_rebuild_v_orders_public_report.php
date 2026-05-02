<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("\n            CREATE OR REPLACE VIEW v_orders_public AS\n            SELECT\n                CONCAT(\n                    'BILL-',\n                    DATE_FORMAT(orders.business_date, '%y%m%d'),\n                    '-',\n                    LPAD(ROW_NUMBER() OVER (PARTITION BY DATE(orders.business_date) ORDER BY orders.completed_at, orders.id), 4, '0')\n                ) AS bill_number,\n                orders.table_id AS table_id,\n                orders.type AS type,\n                orders.subtotal AS subtotal,\n                orders.tax_amount AS tax_amount,\n                orders.total AS total,\n                CAST(orders.business_date AS DATE) AS completed_at\n            FROM orders\n            WHERE ((orders.flag = 0) AND (orders.deleted_at IS NULL) AND (orders.status = 'completed'))\n        ");
    }

    public function down(): void
    {
        DB::statement("\n            CREATE OR REPLACE VIEW v_orders_public AS\n            SELECT \n                id, order_number, bill_number, table_id, \n                customer_name, customer_phone, type, status,\n                subtotal, tax, tax_amount, discount, total,\n                notes, completed_at,\n                created_at, updated_at\n            FROM orders\n            WHERE flag = 0\n              AND deleted_at IS NULL\n        ");
    }
};