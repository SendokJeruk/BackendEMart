<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Detail_Transaction extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function transaction(): HasMany
    {
        return $this->hasMany(related: transaction::class, foreignKey: 'transaction_id');
    }
    public function product(): HasMany
    {
        return $this->hasMany(related: Product::class, foreignKey: 'product_id');
    }
}
