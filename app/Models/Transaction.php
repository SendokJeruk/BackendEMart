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
    protected $hidden = ['timestamps', 'created_at', 'updated_at'];

    public function getRouteKeyName()
    {
        return 'kode_transaksi';
    }


    public function detail_transaction(): HasMany
    {
        return $this->hasMany(related: DetailTransaction::class, foreignKey: 'transaction_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function income()
    {
        return $this->hasOne(Income::class);
    }

    public function alamat_user(): BelongsTo
    {
        return $this->belongsTo(AlamatUser::class);
    }

    public function alamat_toko(): BelongsTo
    {
        return $this->belongsTo(AlamatToko::class);
    }

    public function scopeFilter($query, $request)
    {
        return $query
            ->when($request->has('kode_transaksi'), fn($q) =>
                $q->where('kode_transaksi', $request->kode_transaksi));
    }

}
