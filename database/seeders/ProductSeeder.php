<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductModifier;
use App\Models\Inventory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Makanan
        $nasiGoreng = Product::create([
            'category_id' => 1,
            'name' => 'Nasi Goreng Spesial',
            'description' => 'Nasi goreng dengan telur, ayam, dan sayuran',
            'price' => 25000,
            'cost' => 15000,
            'sku' => 'NG001',
            'is_available' => true,
            'is_active' => true,
        ]);

        Inventory::create([
            'product_id' => $nasiGoreng->id,
            'quantity' => 100,
            'min_quantity' => 10,
            'unit' => 'porsi',
        ]);

        $mieGoreng = Product::create([
            'category_id' => 1,
            'name' => 'Mie Goreng',
            'description' => 'Mie goreng dengan telur dan sayuran',
            'price' => 20000,
            'cost' => 12000,
            'sku' => 'MG001',
            'is_available' => true,
            'is_active' => true,
        ]);

        Inventory::create([
            'product_id' => $mieGoreng->id,
            'quantity' => 100,
            'min_quantity' => 10,
            'unit' => 'porsi',
        ]);

        $ayamGoreng = Product::create([
            'category_id' => 1,
            'name' => 'Ayam Goreng',
            'description' => 'Ayam goreng krispi dengan nasi',
            'price' => 30000,
            'cost' => 18000,
            'sku' => 'AG001',
            'is_available' => true,
            'is_active' => true,
        ]);

        Inventory::create([
            'product_id' => $ayamGoreng->id,
            'quantity' => 50,
            'min_quantity' => 5,
            'unit' => 'porsi',
        ]);

        // Minuman
        $esTeh = Product::create([
            'category_id' => 2,
            'name' => 'Es Teh',
            'description' => 'Es teh manis segar',
            'price' => 5000,
            'cost' => 2000,
            'sku' => 'ET001',
            'is_available' => true,
            'is_active' => true,
        ]);

        ProductVariant::create([
            'product_id' => $esTeh->id,
            'name' => 'Manis',
            'price' => 0,
            'sku' => 'ET001-M',
            'is_available' => true,
        ]);

        ProductVariant::create([
            'product_id' => $esTeh->id,
            'name' => 'Tawar',
            'price' => 0,
            'sku' => 'ET001-T',
            'is_available' => true,
        ]);

        Inventory::create([
            'product_id' => $esTeh->id,
            'quantity' => 200,
            'min_quantity' => 20,
            'unit' => 'gelas',
        ]);

        $jusJeruk = Product::create([
            'category_id' => 2,
            'name' => 'Jus Jeruk',
            'description' => 'Jus jeruk segar',
            'price' => 12000,
            'cost' => 6000,
            'sku' => 'JJ001',
            'is_available' => true,
            'is_active' => true,
        ]);

        Inventory::create([
            'product_id' => $jusJeruk->id,
            'quantity' => 100,
            'min_quantity' => 10,
            'unit' => 'gelas',
        ]);

        $kopi = Product::create([
            'category_id' => 2,
            'name' => 'Kopi',
            'description' => 'Kopi hitam atau susu',
            'price' => 8000,
            'cost' => 3000,
            'sku' => 'KP001',
            'is_available' => true,
            'is_active' => true,
        ]);

        ProductVariant::create([
            'product_id' => $kopi->id,
            'name' => 'Hitam',
            'price' => 0,
            'sku' => 'KP001-H',
            'is_available' => true,
        ]);

        ProductVariant::create([
            'product_id' => $kopi->id,
            'name' => 'Susu',
            'price' => 2000,
            'sku' => 'KP001-S',
            'is_available' => true,
        ]);

        ProductModifier::create([
            'product_id' => $kopi->id,
            'name' => 'Extra Shot',
            'price' => 5000,
            'is_available' => true,
        ]);

        ProductModifier::create([
            'product_id' => $kopi->id,
            'name' => 'Less Sugar',
            'price' => 0,
            'is_available' => true,
        ]);

        Inventory::create([
            'product_id' => $kopi->id,
            'quantity' => 150,
            'min_quantity' => 15,
            'unit' => 'gelas',
        ]);

        // Snack
        $kentangGoreng = Product::create([
            'category_id' => 3,
            'name' => 'Kentang Goreng',
            'description' => 'Kentang goreng crispy',
            'price' => 15000,
            'cost' => 8000,
            'sku' => 'KG001',
            'is_available' => true,
            'is_active' => true,
        ]);

        Inventory::create([
            'product_id' => $kentangGoreng->id,
            'quantity' => 80,
            'min_quantity' => 10,
            'unit' => 'porsi',
        ]);

        // Dessert
        $esCream = Product::create([
            'category_id' => 4,
            'name' => 'Es Cream',
            'description' => 'Es cream vanilla, coklat, atau strawberry',
            'price' => 10000,
            'cost' => 5000,
            'sku' => 'EC001',
            'is_available' => true,
            'is_active' => true,
        ]);

        ProductVariant::create([
            'product_id' => $esCream->id,
            'name' => 'Vanilla',
            'price' => 0,
            'sku' => 'EC001-V',
            'is_available' => true,
        ]);

        ProductVariant::create([
            'product_id' => $esCream->id,
            'name' => 'Coklat',
            'price' => 0,
            'sku' => 'EC001-C',
            'is_available' => true,
        ]);

        ProductVariant::create([
            'product_id' => $esCream->id,
            'name' => 'Strawberry',
            'price' => 2000,
            'sku' => 'EC001-S',
            'is_available' => true,
        ]);

        Inventory::create([
            'product_id' => $esCream->id,
            'quantity' => 60,
            'min_quantity' => 10,
            'unit' => 'cup',
        ]);
    }
}
