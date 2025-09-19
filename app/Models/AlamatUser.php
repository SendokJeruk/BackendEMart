<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlamatUser extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $hidden = ['timestamps', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->HasMany(Transaction::class, 'id_alamat_user');
    }

}
