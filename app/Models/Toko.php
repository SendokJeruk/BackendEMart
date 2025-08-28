<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Toko extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'user_id');
    }

    public function alamatToko(): BelongsTo
    {
        return $this->BelongsTo(AlamatToko::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'user_id', 'user_id');
    }
}
