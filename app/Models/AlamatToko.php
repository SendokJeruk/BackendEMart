<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;


class AlamatToko extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function toko(): HasOne
    {
        return $this->HasOne(Toko::class, 'alamat_toko_id');
    }
}
