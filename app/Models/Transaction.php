<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user(): HasMany
    {
        return $this->hasMany(related: User::class, foreignKey: 'user_id');
    }

    public function detail_transaction(): BelongsTo
    {
        return $this->BelongsTo(Category::class, 'detail-transaction_id');
    }
}
