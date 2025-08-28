<?php

namespace Database\Seeders;

use App\Models\AlamatUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $alamatList = [
            [
                'kode_domestik' => '4866',
                'label' => 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266',
                'province_name' => 'JAWA BARAT',
                'city_name' => 'BANDUNG',
                'district_name' => 'BANDUNG KIDUL',
                'subdistrict_name' => 'BATUNUNGGAL',
                'zip_code' => '40266',
                'detail_alamat' => 'Jl disuatu tempat terdapat orang yang berdiam disana',
            ],
            [
                'kode_domestik' => '4866',
                'label' => 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266',
                'province_name' => 'JAWA BARAT',
                'city_name' => 'BANDUNG',
                'district_name' => 'BANDUNG KIDUL',
                'subdistrict_name' => 'BATUNUNGGAL',
                'zip_code' => '40266',
                'detail_alamat' => 'Jl disuatu tempat terdapat orang yang berdiam disana',
            ],
            [
                'kode_domestik' => '4844',
                'label' => 'CAMPAKA, ANDIR, BANDUNG, JAWA BARAT, 40184',
                'province_name' => 'JAWA BARAT',
                'city_name' => 'BANDUNG',
                'district_name' => 'ANDIR',
                'subdistrict_name' => 'CAMPAKA',
                'zip_code' => '40184',
                'detail_alamat' => 'Jl disuatu tempat terdapat orang yang berdiam disana',
            ],
            [
                'kode_domestik' => '5256',
                'label' => 'CIMAHI, CIMAHI TENGAH, CIMAHI, JAWA BARAT, 40525',
                'province_name' => 'JAWA BARAT',
                'city_name' => 'CIMAHI',
                'district_name' => 'CIMAHI TENGAH',
                'subdistrict_name' => 'CIMAHI',
                'zip_code' => '40525',
                'detail_alamat' => 'Jl disuatu tempat terdapat orang yang berdiam disana',
            ],
            [
                'kode_domestik' => '5256',
                'label' => 'CIMAHI, CIMAHI TENGAH, CIMAHI, JAWA BARAT, 40525',
                'province_name' => 'JAWA BARAT',
                'city_name' => 'CIMAHI',
                'district_name' => 'CIMAHI TENGAH',
                'subdistrict_name' => 'CIMAHI',
                'zip_code' => '40525',
                'detail_alamat' => 'Jl disuatu tempat terdapat orang yang berdiam disana',
            ],
            [
                'kode_domestik' => '4846',
                'label' => 'DUNGUS CARIANG, ANDIR, BANDUNG, JAWA BARAT, 40183',
                'province_name' => 'JAWA BARAT',
                'city_name' => 'BANDUNG',
                'district_name' => 'ANDIR',
                'subdistrict_name' => 'DUNGUS CARIANG',
                'zip_code' => '40183',
                'detail_alamat' => 'Jl disuatu tempat terdapat orang yang berdiam disana',
            ],
            [
                'kode_domestik' => '4877',
                'label' => 'WARUNG MUNCANG, BANDUNG KULON, BANDUNG, JAWA BARAT, 40211',
                'province_name' => 'JAWA BARAT',
                'city_name' => 'BANDUNG',
                'district_name' => 'BANDUNG KULON',
                'subdistrict_name' => 'WARUNG MUNCANG',
                'zip_code' => '40211',
                'detail_alamat' => 'Jl disuatu tempat terdapat orang yang berdiam disana',
            ],
            [
                'kode_domestik' => '8427',
                'label' => 'DAGO, PARUNG PANJANG, BOGOR, JAWA BARAT, 16360',
                'province_name' => 'JAWA BARAT',
                'city_name' => 'BOGOR',
                'district_name' => 'PARUNG PANJANG',
                'subdistrict_name' => 'DAGO',
                'zip_code' => '16360',
                'detail_alamat' => 'Jl disuatu tempat terdapat orang yang berdiam disana',
            ],
            [
                'kode_domestik' => '8427',
                'label' => 'DAGO, PARUNG PANJANG, BOGOR, JAWA BARAT, 16360',
                'province_name' => 'JAWA BARAT',
                'city_name' => 'BOGOR',
                'district_name' => 'PARUNG PANJANG',
                'subdistrict_name' => 'DAGO',
                'zip_code' => '16360',
                'detail_alamat' => 'Jl disuatu tempat terdapat orang yang berdiam disana',
            ],
            [
                'kode_domestik' => '8119',
                'label' => 'BUBULAK, BOGOR BARAT - KOTA, BOGOR, JAWA BARAT, 16115',
                'province_name' => 'JAWA BARAT',
                'city_name' => 'BOGOR',
                'district_name' => 'BOGOR BARAT - KOTA',
                'subdistrict_name' => 'BUBULAK',
                'zip_code' => '16115',
                'detail_alamat' => 'Jl disuatu tempat terdapat orang yang berdiam disana',
            ],
            [
                'kode_domestik' => '4872',
                'label' => 'CIGONDEWAH KALER, BANDUNG KULON, BANDUNG, JAWA BARAT, 40214',
                'province_name' => 'JAWA BARAT',
                'city_name' => 'BANDUNG',
                'district_name' => 'BANDUNG KULON',
                'subdistrict_name' => 'CIGONDEWAH KALER',
                'zip_code' => '40214',
                'detail_alamat' => 'Jl disuatu tempat terdapat orang yang berdiam disana',
            ],
            [
                'kode_domestik' => '4872',
                'label' => 'CIGONDEWAH KALER, BANDUNG KULON, BANDUNG, JAWA BARAT, 40214',
                'province_name' => 'JAWA BARAT',
                'city_name' => 'BANDUNG',
                'district_name' => 'BANDUNG KULON',
                'subdistrict_name' => 'CIGONDEWAH KALER',
                'zip_code' => '40214',
                'detail_alamat' => 'Jl disuatu tempat terdapat orang yang berdiam disana',
            ],
            [
                'kode_domestik' => '4912',
                'label' => 'PAJAJARAN, CICENDO, BANDUNG, JAWA BARAT, 40173',
                'province_name' => 'JAWA BARAT',
                'city_name' => 'BANDUNG',
                'district_name' => 'CICENDO',
                'subdistrict_name' => 'PAJAJARAN',
                'zip_code' => '40173',
                'detail_alamat' => 'Jl disuatu tempat terdapat orang yang berdiam disana',
            ],
            [
                'kode_domestik' => '4949',
                'label' => 'BRAGA, SUMUR BANDUNG, BANDUNG, JAWA BARAT, 40111',
                'province_name' => 'JAWA BARAT',
                'city_name' => 'BANDUNG',
                'district_name' => 'SUMUR BANDUNG',
                'subdistrict_name' => 'BRAGA',
                'zip_code' => '40111',
                'detail_alamat' => 'Jl disuatu tempat terdapat orang yang berdiam disana',
            ],
            [
                'kode_domestik' => '4911',
                'label' => 'HUSEN SASTRANEGARA, CICENDO, BANDUNG, JAWA BARAT, 40174',
                'province_name' => 'JAWA BARAT',
                'city_name' => 'BANDUNG',
                'district_name' => 'CICENDO',
                'subdistrict_name' => 'HUSEN SASTRANEGARA',
                'zip_code' => '40174',
                'detail_alamat' => 'Jl disuatu tempat terdapat orang yang berdiam disana',
            ],
        ];

        User::factory()->count(15)->create()->each(function ($user) use ($alamatList) {
            $alamat = $alamatList[array_rand($alamatList)];
            AlamatUser::create(array_merge($alamat, [
                'user_id' => $user->id,
                'nama_penerima' => fake()->name()
            ]));
        });

        User::create([
            'name' => 'ini user test',
            'email' => 'user@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'), // password
            'remember_token' => Str::random(10),
            'no_telp' => fake()->numerify('08##########'),
            'role_id' => Role::inRandomOrder()->first()->id,
        ]);
    }
}
