<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart_detail extends Model
{
    use HasFactory;
    protected $guarded = [];


        public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

        public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }


}
