<?php
namespace App\Http\Requests\Alamat;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class StoreRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'kode_domestik'    => 'required|integer',
            'label'            => 'required|string|max:100',
            'province_name'    => 'required|string|max:100',
            'city_name'        => 'required|string|max:100',
            'district_name'    => 'required|string|max:100',
            'subdistrict_name' => 'required|string|max:100',
            'zip_code'         => 'required|digits:5',
            'detail_alamat'    => 'required|string|max:255',
        ];
    }

    public function attributes() {
        return [
            'kode_domestik'    => 'kode domestik',
            'label'            => 'label alamat',
            'province_name'    => 'provinsi',
            'city_name'        => 'kota/kabupaten',
            'district_name'    => 'kecamatan',
            'subdistrict_name' => 'kelurahan/desa',
            'zip_code'         => 'kode pos',
            'detail_alamat'    => 'detail alamat',
        ];
    }

    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'max' => ':attribute maksimal :max karakter.',
            'digits' => ':attribute harus terdiri dari :digits digit.',
        ];
    }
}
