<?php
namespace App\Http\Requests\Withdraw;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class SubmitWithdrawRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'jumlah' => 'required|numeric|min:10000',
            'metode' => 'required|string|in:bank_transfer,gopay,ovo,dana,shopeePay',
            'rekening_tujuan' => 'required|string|max:50',
            'catatan' => 'nullable|string|max:255',
        ];
    }
        public function attributes() {
        return [
            'jumlah' => 'jumlah',
            'metode' => 'metode penarikan',
            'rekening_tujuan' => 'rekening tujuan',
            'catatan' => 'catatan',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
            'numeric' => ':attribute harus berupa angka.',
            'min' => ':attribute minimal :min.',
            'string' => ':attribute harus berupa teks.',
            'in' => 'metode tidak valid.',
            'max' => ':attribute maksimal :max karakter.',
        ];
    }
}
