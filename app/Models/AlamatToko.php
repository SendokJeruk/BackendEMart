<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlamatToko extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function toko(): BelongsTo
    {
        return $this->BelongsTo(Toko::class, 'user_id');
    }
}
