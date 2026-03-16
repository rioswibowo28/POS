<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Makanan',
                'description' => 'Menu makanan utama',
                'is_active' => true,
            ],
            [
                'name' => 'Minuman',
                'description' => 'Menu minuman',
                'is_active' => true,
            ],
            [
                'name' => 'Snack',
                'description' => 'Menu cemilan dan snack',
                'is_active' => true,
            ],
            [
                'name' => 'Dessert',
                'description' => 'Menu penutup dan dessert',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
