<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Opsi A: Database Views untuk pihak ketiga (device perekaman pajak)
 * 
 * Views ini HANYA menampilkan order dengan flag=0 (order normal/kena pajak).
 * Order dengan flag=1 (tanpa pajak) tidak terlihat sama sekali.
 * 
 * Tabel yang di-expose:
 * - v_orders_public: Orders flag=0 saja
 * - v_order_items_public: Items dari orders flag=0 saja
 * - v_payments_public: Payments dari orders flag=0 saja
 */
return new class extends Migration
{
    public function up(): void
    {
        // View: Orders publik (flag=0 only)
        DB::statement("
            CREATE OR REPLACE VIEW v_orders_public AS
            SELECT 
                id, order_number, bill_number, table_id, 
                customer_name, customer_phone, type, status,
                subtotal, tax, tax_amount, discount, total,
                notes, completed_at,
                created_at, updated_at
            FROM orders
            WHERE flag = 0
              AND deleted_at IS NULL
        ");

        // View: Order Items publik (hanya dari orders flag=0)
        DB::statement("
            CREATE OR REPLACE VIEW v_order_items_public AS
            SELECT 
                oi.id, oi.order_id, oi.product_id, oi.product_variant_id,
                oi.name, oi.price, oi.quantity,
                oi.modifiers, oi.notes,
                oi.created_at, oi.updated_at
            FROM order_items oi
            INNER JOIN orders o ON o.id = oi.order_id
            WHERE o.flag = 0
              AND o.deleted_at IS NULL
        ");

        // View: Payments publik (hanya dari orders flag=0)
        DB::statement("
            CREATE OR REPLACE VIEW v_payments_public AS
            SELECT 
                p.id, p.order_id, p.payment_number,
                p.method, p.status, p.amount,
                p.received_amount, p.change_amount,
                p.reference_number, p.notes,
                p.created_at, p.updated_at
            FROM payments p
            INNER JOIN orders o ON o.id = p.order_id
            WHERE o.flag = 0
              AND o.deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_payments_public");
        DB::statement("DROP VIEW IF EXISTS v_order_items_public");
        DB::statement("DROP VIEW IF EXISTS v_orders_public");
    }
};
