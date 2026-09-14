<?php
namespace App\Http\Requests\CategoryProduct;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class UpdateRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() { return [ 'category_id' => 'nullable', 'product_id' => 'nullable' ]; }
}
