<?php
namespace App\Http\Requests\Toko;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class UpdateAlamatRequest extends FormRequest {
    public function authorize() {
        return $this->route('toko')->user_id === auth()->id();
    }
    public function rules() {
        return [
            'kode_domestik'     => 'required',
            'label'             => 'required',
            'province_name'     => 'required',
            'city_name'         => 'required',
            'district_name'     => 'required',
            'subdistrict_name'  => 'required',
            'zip_code'          => 'required',
            'detail_alamat'     => 'required',
        ];
    }

    public function attributes()
    {
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
        ];
    }
}
