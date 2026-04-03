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
            'kode_domestik'     => 'nullable',
            'label'             => 'nullable',
            'province_name'     => 'nullable',
            'city_name'         => 'nullable',
            'district_name'     => 'nullable',
            'subdistrict_name'  => 'nullable',
            'zip_code'          => 'nullable',
            'detail_alamat'     => 'nullable',
        ];
    }
}
