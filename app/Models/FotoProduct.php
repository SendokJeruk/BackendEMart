<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FotoProduct extends Model
{
    use HasFactory;
    protected $fillable = [
        'foto_id',
        'product_id',
    ];
    protected $hidden = ['timestamps', 'created_at', 'updated_at'];
}
