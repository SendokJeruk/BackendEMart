<?php
namespace App\Http\Requests\FotoProduct;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class UpdateRequest extends FormRequest {
    public function authorize() {
        return $this->route('fotoProduct')->product->user_id === auth()->id();
    }
    public function rules() {
        return [
            'foto_id'    => 'nullable',
            'product_id' => 'nullable',
        ];
    }
}
