<?php
namespace App\Http\Requests\Toko;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class StoreRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'nama_toko' => 'required|string|max:100',
            'deskripsi' => 'required|string|max:255',
            'no_telp'   => 'required|numeric|digits_between:10,13',
            'kode_domestik'    => 'required',
            'label'            => 'required',
            'province_name'    => 'required',
            'city_name'        => 'required',
            'district_name'    => 'required',
            'subdistrict_name' => 'required',
            'zip_code'         => 'required',
            'detail_alamat'    => 'required',
        ];
    }
        public function attributes() {
        return [
            'nama_toko' => 'nama toko',
            'deskripsi' => 'deskripsi',
            'no_telp' => 'nomor telepon',
            'kode_domestik' => 'kode domestik',
            'label' => 'label',
            'province_name' => 'provinsi',
            'city_name' => 'kota/kabupaten',
            'district_name' => 'kecamatan',
            'subdistrict_name' => 'kelurahan/desa',
            'zip_code' => 'kode pos',
            'detail_alamat' => 'detail alamat',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'numeric' => ':attribute harus berupa angka.',
            'digits_between' => ':attribute harus terdiri dari :min sampai :max digit.',
        ];
    }
}
