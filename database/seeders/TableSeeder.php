<?php

namespace Database\Seeders;

use App\Models\Table;
use App\Enums\TableStatus;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama (disable foreign key checks)
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Table::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Buat 20 meja (5 meja per baris)
        $tables = [];
        
        for ($i = 1; $i <= 20; $i++) {
            // Kapasitas bervariasi: 2, 4, 4, 6, 4 (pattern berulang)
            $capacity = match($i % 5) {
                1 => 2,  // Meja 1, 6, 11, 16
                2 => 4,  // Meja 2, 7, 12, 17
                3 => 4,  // Meja 3, 8, 13, 18
                4 => 6,  // Meja 4, 9, 14, 19
                0 => 4,  // Meja 5, 10, 15, 20
            };
            
            $tables[] = [
                'number' => (string)$i,
                'capacity' => $capacity,
                'status' => TableStatus::AVAILABLE,
                'is_active' => true,
            ];
        }

        foreach ($tables as $table) {
            Table::create($table);
        }
    }
}
