<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'total_jumlah',
        'total_harga',
    ];
    protected $hidden = ['timestamps', 'created_at', 'updated_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function cart_detail(): HasMany
    {
        return $this->HasMany(Cart_detail::class);
    }
    public function toko()
    {
        return $this->hasMany(Toko::class, 'user_id', 'user_id');
    }
}
