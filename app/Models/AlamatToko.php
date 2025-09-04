<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Rlations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class AlamatToko extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['timestamps', 'created_at', 'updated_at'];

    public function toko(): HasOne
    {
        return $this->HasOne(Toko::class, 'alamat_toko_id');
    }
    public function transactions(): HasMany
    {
        return $this->HasMany(Transaction::class, 'id_alamat_toko');
    }
}
