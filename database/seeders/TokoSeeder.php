<?php

namespace Database\Seeders;

use App\Models\Toko;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TokoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sellers = User::whereHas('role', function ($query) {
            $query->where('nama_role', 'seller');
        })->get();

        foreach ($sellers as $seller) {
            Toko::factory()->create([
                'user_id' => $seller->id,
                'no_telp' => $seller->no_telp,
            ]);
        }
    }
}
