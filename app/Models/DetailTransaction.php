<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DetailTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_id',
        'product_id',
        'harga',
        'jumlah',
        'subtotal',
        'totalberat',
    ];
    protected $hidden = ['timestamps', 'created_at', 'updated_at'];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function detailIncome()
    {
        return $this->hasOne(DetailIncome::class);
    }
    public function rating()
    {
        return $this->hasOne(Rating::class, 'detail_transaction_id');
    }

    public function scopeFilter($query, $request)
    {
        return $query
            ->when($request->filled('transaction_id'), fn($q) =>
            $q->where('transaction_id', $request->transaction_id));
    }
}
