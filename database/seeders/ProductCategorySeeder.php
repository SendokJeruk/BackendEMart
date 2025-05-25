<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $products = Product::all();
        $categories = Category::all();

        if ($categories->isEmpty() || $products->isEmpty()) {
            return;
        }

        foreach ($products as $product) {
            $randomCount = rand(1, $categories->count());
            $randomCategoryIds = $categories->random($randomCount)->pluck('id')->toArray();
            $product->categories()->syncWithoutDetaching($randomCategoryIds);
        }
    }
}
