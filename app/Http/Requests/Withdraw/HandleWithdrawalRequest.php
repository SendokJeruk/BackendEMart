<?php
namespace App\Http\Requests\Withdraw;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class HandleWithdrawalRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'status' => 'required|in:accepted,rejected',
        ];
    }
        public function attributes() {
        return [
            'status' => 'status',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
            'in' => 'status hanya dapat berupa "accepted" atau "rejected".',
        ];
    }
}
