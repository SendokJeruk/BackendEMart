<?php

namespace Database\Seeders;

use App\Models\Rating;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::factory()->count(30)->create()->each(function ($product) {
            Rating::factory()->count(rand(3, 10))->create([
                'product_id' => $product->id,
            ]);
        });
    }
}
