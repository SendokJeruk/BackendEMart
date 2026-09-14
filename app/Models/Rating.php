<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rating extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'product_id',
        'detail_transaction_id',
        'rating',
        'deskripsi',
        'foto_review'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function detailTransaction()
    {
        return $this->belongsTo(DetailTransaction::class, 'detail_transaction_id');
    }
}

