<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Flash Sale Item - Limited Edition Smartphone',
            'price' => 599.99,
            'stock' => 100, // Finite stock for flash sale
        ]);

        // Optional: Add a few more products for testing
        Product::create([
            'name' => 'Wireless Headphones',
            'price' => 149.99,
            'stock' => 50,
        ]);

        Product::create([
            'name' => 'Smart Watch',
            'price' => 299.99,
            'stock' => 25,
        ]);
    }
}