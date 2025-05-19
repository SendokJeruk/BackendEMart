<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $guarded = [];

        public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
        public function cart_detail(): HasMany
    {
        return $this->HasMany(cart_detail::class, 'cartDetail_id');
    }

}
