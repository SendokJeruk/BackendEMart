<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function detail_transaction(): HasMany
    {
        return $this->hasMany(related: detail_transaction::class, foreignKey: 'detail-transaction_id');
    }
    public function rating(): HasMany
    {
        return $this->hasMany(related: rating::class, foreignKey: 'rating_id');
    }

    public function category(): BelongsTo
    {
        return $this->BelongsTo(Category::class, 'category_id');
    }
    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'user_id');
    }
}

