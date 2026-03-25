<?php

namespace App\Models;

use App\Http\Controllers\API\ProductController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryProduct extends Model
{
    protected $table = 'category_products';

    protected $fillable = [
        'category_id',
        'product_id',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

