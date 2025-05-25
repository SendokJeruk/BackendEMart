<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => function () {
                $seller = User::whereHas('role', function ($query) {
                    $query->where('nama_role', 'seller');
                })->inRandomOrder()->first();

                if ($seller) {
                    return $seller->id;
                } else {
                    return User::factory()->create(['role_id' => \App\Models\Role::where('nama_role', 'seller')->first()->id])->id;
                }
            },
            'nama_product' => function () {
                $adjectives = [
                    'Super', 'Mega', 'Ultra', 'Eco', 'Pro', 'Smart', 'Advanced', 'Classic', 'Modern',
                    'Vintage', 'Elegant', 'Compact', 'Portable', 'Durable', 'Lightweight', 'Wireless',
                    'Waterproof', 'Limited Edition', 'Premium', 'Custom', 'Sleek', 'Powerful', 'Affordable'
                ];

                $productTypes = [
                    'T-Shirt', 'Sepatu', 'Jam Tangan', 'Tas', 'Kacamata', 'Jaket', 'Celana Jeans',
                    'Topi Baseball', 'Kaos Kaki', 'Sweater Hoodie', 'Tas Selempang', 'Sepatu Formal',
                    'Jaket Denim', 'Jam Tangan Digital', 'Kacamata Renang', 'Rompi Safety', 'Tas Laptop',
                    'Sepatu Lari', 'Baju Renang', 'Sarung Tangan', 'Dompet', 'Kalung', 'Gelang', 'Belt'
                ];

                return fake()->randomElement($adjectives) . ' ' . fake()->randomElement($productTypes);
            },
            'deskripsi' => fake()->paragraph(),
            'harga' => (string) fake()->numberBetween(10000, 1000000),
            'stock' => fake()->numberBetween(1, 100),
            'berat' => fake()->numberBetween(100, 5000),
            'foto_cover' => function (array $attributes) {
                $text = str_replace(' ', '+', $attributes['nama_product']);
                $bgColors = ['0044ee', 'ee4400', '44ee00', 'ee00bb', '00eeee'];
                $textColors = ['ffffff', '000000'];
                $bg = $bgColors[array_rand($bgColors)];
                $textColor = $textColors[array_rand($textColors)];

                return "https://dummyimage.com/640x480/{$bg}/{$textColor}.png&text={$text}";
            },
            'status_produk' => fake()->randomElement(['draft', 'publish']),
        ];
    }
}
