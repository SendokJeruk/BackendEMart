<?php
namespace App\Http\Requests\Role;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class UpdateRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'nama_role' => 'nullable',
        ];
    }
}
