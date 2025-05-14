<?php

namespace App\Models;

use App\Models\User;
use App\Models\Category;
use App\Models\DetailTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];




    public function detail_transaction(): HasMany
    {
        return $this->hasMany(related: DetailTransaction::class, foreignKey: 'product_id');
    }
        public function rating(): HasMany
        {
            return $this->hasMany(related: rating::class, foreignKey: 'rating_id');
        }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_products', 'product_id', 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function foto(): BelongsToMany
    {
        return $this->belongsToMany(Foto::class, 'foto_products');
    }

    public function cart_detail(): HasMany
    {
        return $this->HasMany(Cart_detail::class, 'cartDetail_id');
    }

}

