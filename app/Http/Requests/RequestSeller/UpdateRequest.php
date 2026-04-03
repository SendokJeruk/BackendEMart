<?php
namespace App\Http\Requests\RequestSeller;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class UpdateRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'status' => 'required|in:accepted,rejected',
            'note' => 'required',
        ];
    }
        public function attributes() {
        return [
            'status' => 'status',
            'note' => 'catatan',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
            'in' => 'status hanya boleh "accepted" atau "rejected".',
        ];
    }
}
