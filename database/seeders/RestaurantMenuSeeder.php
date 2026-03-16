<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class RestaurantMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear all existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        DB::table('order_items')->truncate();
        DB::table('payments')->truncate();
        DB::table('orders')->truncate();
        DB::table('products')->truncate();
        DB::table('categories')->truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('All existing data cleared.');
        
        // Create Categories
        $minuman = Category::create([
            'name' => 'Minuman',
            'description' => 'Minuman dingin dan panas',
            'is_active' => true,
        ]);
        
        $mieGorengan = Category::create([
            'name' => 'Mie & Gorengan',
            'description' => 'Mie goreng, mie kuah, dan gorengan',
            'is_active' => true,
        ]);
        
        $nasiCampur = Category::create([
            'name' => 'Nasi Campur & Gudeg',
            'description' => 'Nasi campur dan gudeg',
            'is_active' => true,
        ]);
        
        $sayurAsem = Category::create([
            'name' => 'Sayur Asem',
            'description' => 'Nasi sayur asem dengan berbagai lauk',
            'is_active' => true,
        ]);
        
        $rawon = Category::create([
            'name' => 'Rawon',
            'description' => 'Nasi rawon dengan berbagai lauk',
            'is_active' => true,
        ]);
        
        $pecel = Category::create([
            'name' => 'Pecel',
            'description' => 'Nasi pecel dengan berbagai lauk',
            'is_active' => true,
        ]);
        
        $kareSoto = Category::create([
            'name' => 'Kare & Soto',
            'description' => 'Kare ayam dan soto',
            'is_active' => true,
        ]);
        
        $penyetKrengsengan = Category::create([
            'name' => 'Penyet & Krengsengan',
            'description' => 'Ayam penyet dan krengsengan',
            'is_active' => true,
        ]);
        
        $tambahan = Category::create([
            'name' => 'Tambahan / Lauk',
            'description' => 'Lauk tambahan dan pelengkap',
            'is_active' => true,
        ]);
        
        $this->command->info('Categories created.');
        
        // MINUMAN
        $minumanItems = [
            ['Es Soda Gembira', 15000],
            ['Es Teh', 5000],
            ['Es Teh Jumbo', 10000],
            ['Es Teh Tawar', 4000],
            ['Es Susu', 9000],
            ['Susu Anget', 8000],
            ['Susu Jahe', 10000],
            ['Es Susu Jumbo', 16000],
            ['Es Tomat', 16000],
            ['Tomat Panas', 9000],
            ['Es Jeruk', 7000],
            ['Jeruk Panas', 6000],
            ['Es Sirup', 5000],
            ['Es Sirup Jumbo', 10000],
            ['Es Jeruk Jumbo', 14000],
            ['Es Joshua', 9000],
            ['Es Extrajos', 7000],
            ['Es Joshua Jumbo', 12000],
            ['Teh Panas Tawar', 3000],
            ['Teh Panas Manis', 4000],
            ['Kopi Panas', 6000],
            ['Kopi Susu', 9000],
            ['Kopi Jahe', 12000],
            ['Wedang Jahe', 9000],
            ['Wedang Jahe Serai', 12000],
        ];
        
        foreach ($minumanItems as $item) {
            Product::create([
                'category_id' => $minuman->id,
                'name' => $item[0],
                'description' => $item[0],
                'price' => $item[1],
                'cost' => $item[1] * 0.4, // Estimated cost 40% of price
                'is_available' => true,
                'is_active' => true,
            ]);
        }
        
        $this->command->info('Minuman items created.');
        
        // MIE & GORENGAN
        $mieItems = [
            ['Mie Goreng', 18000],
            ['Mie Godog', 18000],
            ['Mihun Goreng', 18000],
            ['Mihun Kuah', 18000],
            ['Cap Cay', 18000],
            ['Cap Cay Goreng', 21000],
            ['Nasi Goreng', 18000],
        ];
        
        foreach ($mieItems as $item) {
            Product::create([
                'category_id' => $mieGorengan->id,
                'name' => $item[0],
                'description' => $item[0],
                'price' => $item[1],
                'cost' => $item[1] * 0.4,
                'is_available' => true,
                'is_active' => true,
            ]);
        }
        
        $this->command->info('Mie & Gorengan items created.');
        
        // NASI CAMPUR & GUDEG
        $nasiCampurItems = [
            ['Nasi Campur Daging', 21000],
            ['Nasi Campur Kare', 21000],
            ['Nasi Campur Empal', 21000],
            ['Nasi Campur Ayam Goreng', 31000],
            ['Nasi Gudeg', 18000],
            ['Nasi Gudeg Komplit (Telur + Ayam)', 23000],
        ];
        
        foreach ($nasiCampurItems as $item) {
            Product::create([
                'category_id' => $nasiCampur->id,
                'name' => $item[0],
                'description' => $item[0],
                'price' => $item[1],
                'cost' => $item[1] * 0.4,
                'is_available' => true,
                'is_active' => true,
            ]);
        }
        
        $this->command->info('Nasi Campur & Gudeg items created.');
        
        // SAYUR ASEM
        $sayurAsemItems = [
            ['Nasi Sayur Asem Bandeng', 18000],
            ['Nasi Sayur Asem Empal', 21000],
            ['Nasi Sayur Asem Ayam Goreng', 31000],
            ['Sayur Asem Saja', 5000],
        ];
        
        foreach ($sayurAsemItems as $item) {
            Product::create([
                'category_id' => $sayurAsem->id,
                'name' => $item[0],
                'description' => $item[0],
                'price' => $item[1],
                'cost' => $item[1] * 0.4,
                'is_available' => true,
                'is_active' => true,
            ]);
        }
        
        $this->command->info('Sayur Asem items created.');
        
        // RAWON
        $rawonItems = [
            ['Nasi Rawon', 18000],
            ['Nasi Rawon Empal', 21000],
            ['Nasi Rawon Telur Asin', 19000],
            ['Kuah Rawon Saja', 5000],
        ];
        
        foreach ($rawonItems as $item) {
            Product::create([
                'category_id' => $rawon->id,
                'name' => $item[0],
                'description' => $item[0],
                'price' => $item[1],
                'cost' => $item[1] * 0.4,
                'is_available' => true,
                'is_active' => true,
            ]);
        }
        
        $this->command->info('Rawon items created.');
        
        // PECEL
        $pecelItems = [
            ['Nasi Pecel Ikan Krengsengan', 18000],
            ['Nasi Pecel Ikan Empal', 21000],
            ['Nasi Pecel Ikan Ayam Goreng', 31000],
            ['Nasi Pecel Ikan Ayam Kare', 21000],
            ['Nasi Pecel Lele', 18000],
        ];
        
        foreach ($pecelItems as $item) {
            Product::create([
                'category_id' => $pecel->id,
                'name' => $item[0],
                'description' => $item[0],
                'price' => $item[1],
                'cost' => $item[1] * 0.4,
                'is_available' => true,
                'is_active' => true,
            ]);
        }
        
        $this->command->info('Pecel items created.');
        
        // KARE & SOTO
        $kareSotoItems = [
            ['Nasi Kare Ayam', 18000],
            ['Kare Saja', 15000],
            ['Nasi Kare Ayam Penyet', 25000],
            ['Nasi Soto Ayam', 18000],
            ['Nasi Soto Daging', 18000],
            ['Nasi Soto Daging Pisah', 22000],
            ['Kuah Soto Saja', 5000],
        ];
        
        foreach ($kareSotoItems as $item) {
            Product::create([
                'category_id' => $kareSoto->id,
                'name' => $item[0],
                'description' => $item[0],
                'price' => $item[1],
                'cost' => $item[1] * 0.4,
                'is_available' => true,
                'is_active' => true,
            ]);
        }
        
        $this->command->info('Kare & Soto items created.');
        
        // PENYET & KRENGSENGAN
        $penyetItems = [
            ['Nasi Krengsengan Daging', 18000],
            ['Nasi Krengsengan Ati Ayam', 18000],
            ['Ayam Penyet Nasi', 25000],
            ['Ayam Penyet Tanpa Nasi', 23000],
            ['Nasi Ayam Goreng', 25000],
            ['Empal Penyet', 25000],
            ['Bandeng Penyet', 21000],
            ['Nasi Goreng Mawut', 18000],
        ];
        
        foreach ($penyetItems as $item) {
            Product::create([
                'category_id' => $penyetKrengsengan->id,
                'name' => $item[0],
                'description' => $item[0],
                'price' => $item[1],
                'cost' => $item[1] * 0.4,
                'is_available' => true,
                'is_active' => true,
            ]);
        }
        
        $this->command->info('Penyet & Krengsengan items created.');
        
        // TAMBAHAN / LAUK
        $tambahanItems = [
            ['Ayam Suwir', 10000],
            ['Nasi Putih', 5000],
            ['Tahu', 1000],
            ['Tempe', 1000],
            ['Telur Asin', 5000],
            ['Krupuk Udang', 2000],
            ['Krupuk Putih', 1000],
            ['Telur (Dadar/Ceplok/Rebus)', 5000],
            ['Sambal di Cobek', 5000],
            ['Lalapan Saja', 5000],
            ['Peyek', 5000],
            ['Paru', 3000],
            ['Usus', 10000],
        ];
        
        foreach ($tambahanItems as $item) {
            Product::create([
                'category_id' => $tambahan->id,
                'name' => $item[0],
                'description' => $item[0],
                'price' => $item[1],
                'cost' => $item[1] * 0.4,
                'is_available' => true,
                'is_active' => true,
            ]);
        }
        
        $this->command->info('Tambahan / Lauk items created.');
        
        $this->command->info('✅ All menu items have been successfully created!');
        $this->command->info('Total Categories: 9');
        $this->command->info('Total Products: ' . Product::count());
    }
}
